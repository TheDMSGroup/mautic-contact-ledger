<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic Community
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactLedgerBundle\Entity;

use Mautic\CampaignBundle\Entity\Campaign;
use Mautic\CoreBundle\Entity\CommonRepository;
use Mautic\LeadBundle\Entity\Lead as Contact;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * class LedgerEntryRepository.
 */
class LedgerEntryRepository extends CommonRepository
{
    const MAUTIC_CONVERSION_STATUS = 'converted';

    /**
     * @param Campaign $campaign
     *
     * @return array
     */
    public function getCampaignChartData(Campaign $campaign, DateTime $dateFrom = null, DateTime $dateTo = null)
    {
        $builder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $builder
            ->select(
                'SUM(cost) as cost',
                'SUM(revenue) as revenue',
                'SUM(cost)-SUM(revenue) as profit',
                'DATE_FORMAT(date_added, "%b %e, %y") as label'
            )
            ->from('contact_ledger')
            ->where(
                'id = :id',
                'date_added BETWEEN :from AND :to'
            )
            ->groupBy('DATE_FORMAT(date_added, "%Y%m%d")')
            ->orderBy('date_added', 'ASC');

        $query  = $builder->getSQL();
        $params = [
            'id'   => $campaign->getId(),
            'from' => $dateFrom,
            'to'   => $dateTo,
        ];

        try {
            $results = $this->getEntityManager()->getConnection()->fetchAll($query, $params);
        } catch (\Exception $e) {
            die($e->getFile().$e->getLine().$e->getMessage());
        }

        $labels = $costs = $revenues = $profits = [];

        foreach ($results as $result) {
            list($costs[], $revenues[], $profites[], $labels[]) = $result;
        }

        return [
            'labels'   => $labels,
            'datasets' => [
                [
                    'label' => 'Cost',
                    'data'  => $costs,
                ],
                [
                    'label' => 'Reveue',
                    'data'  => $revenues,
                ],
                [
                    'label' => 'Profit',
                    'data'  => $profits,
                ],
            ],
        ];
    }

    /**
     * @param Contact $contact
     *
     * @return string
     */
    public function getContactRevenue(Contact $contact)
    {
        return '';
    }

    /**
     * @param $params
     *
     * @return array
     */
    public function getDashboardRevenueWidgetData($params)
    {
        $results            = $financials = [];

        // first get a count of leads that were ingested during selected date range
        $q = $this->_em->getConnection()->createQueryBuilder()
            ->select('COUNT(l.id) as count')
            ->from(MAUTIC_TABLE_PREFIX.'leads', 'l');

        if ($params['dateFrom']) {
            $q->where(
                $q->expr()->gte('l.date_added', ':dateFrom')
            );
            $q->setParameter('dateFrom', $params['dateFrom']);
        }

        if ($params['dateTo']) {
            if (!$params['dateFrom']) {
                $q->where(
                    $q->expr()->lte('l.date_added', ':dateTo')
                );
            } else {
                $q->andWhere(
                    $q->expr()->lte('l.date_added', ':dateTo')
                );
            }
            $q->setParameter('dateTo', $params['dateTo']);
        }
        $count = $q->execute()->fetch();
        // now get ledger data for selected leads
        if ($count['count']) {
            // get the actual IDs to use from this date range

            $q->resetQueryPart('select');
            $q->select('l.id');

            $leads = $q->execute()->fetchAll();
            $leads = array_column($leads, 'id');

            // get financials from ledger based on returned Lead list
            $f = $this->_em->getConnection()->createQueryBuilder();
            $f->select(
                'c.name, c.is_published, c.id as campaign_id, SUM(cl.cost) as cost, SUM(cl.revenue) as revenue, COUNT(cl.contact_id) as received'
            )->from(MAUTIC_TABLE_PREFIX.'contact_ledger', 'cl');

            $f->groupBy('cl.campaign_id');

            // join Campaign table to get name and publish status
            $f->join('cl', MAUTIC_TABLE_PREFIX.'campaigns', 'c', 'c.id = cl.campaign_id');

            $f->orderBy('COUNT(cl.contact_id)', 'DESC');

            if (isset($params['limit'])) {
                $f->setMaxResults($params['limit']);
            }

            $financials = $f->execute()->fetchAll();

            // get conversions from ledger based on class and activity
            $c = $this->_em->getConnection()->createQueryBuilder();
            $c->select('COUNT(activity) as converted, campaign_id')
                ->from(MAUTIC_TABLE_PREFIX.'contact_ledger', 'cl');

            $c->groupBy('cl.campaign_id');
            $c->where(
                $c->expr()->in('cl.contact_id', $leads)
            );
            $c->andWhere(
                $c->expr()->eq('cl.class_name', ':ContactClientModel'),
                $c->expr()->eq('cl.activity', ':MAUTIC_CONVERSION_LABEL')
            );
            $c->setParameter('ContactClientModel', 'ContactClientModel');
            $c->setParameter('MAUTIC_CONVERSION_LABEL', self::MAUTIC_CONVERSION_STATUS);

            $conversions = $c->execute()->fetchAll();
            $conversions = array_column($conversions, 'converted', 'campaign_id');

            foreach ($financials as $financial) {
                // must be ordered as active, id, name, received, converted, revenue, cost, gm, margin, ecpm
                $financial['revenue']   = floatval($financial['revenue']);
                $financial['cost']      = floatval($financial['cost']);
                $financial['gm']        = $financial['revenue'] - $financial['cost'];
                $financial['margin']    = $financial['revenue'] ? number_format(
                    ($financial['gm'] / $financial['revenue']) * 100,
                    2,
                    '.',
                    ','
                ) : 0;
                $financial['ecpm']      = number_format($financial['gm'] / 1000, 4, '.', ',');
                $financial['received']  = intval($financial['received']);
                $financial['converted'] = isset($conversions[$financial['campaign_id']]) ? $conversions[$financial['campaign_id']] : 0;
                $results['rows'][]      = [
                    $financial['is_published'],
                    $financial['campaign_id'],
                    $financial['name'],
                    $financial['received'],
                    $financial['converted'],
                    $financial['revenue'],
                    $financial['cost'],
                    $financial['gm'],
                    $financial['margin'],
                    $financial['ecpm'],
                ];
                //
                // $results['summary']['gmTotal']        = $results['summary']['gmTotal'] + $financial['gm'];
                // $results['summary']['costTotal']      = $results['summary']['costTotal'] + $financial['cost'];
                // $results['summary']['revenueTotal']   = $results['summary']['revenueTotal'] + $financial['revenue'];
                // $results['summary']['ecpmTotal']      = $results['summary']['gmTotal'] / 1000;
                // $results['summary']['marginTotal']    = $results['summary']['revenueTotal'] ? ($results['summary']['gmTotal'] / $results['summary']['revenueTotal']) * 100 : 0;
                // $results['summary']['receivedTotal']  = $results['summary']['receivedTotal'] + $financial['received'];
                // $results['summary']['convertedTotal'] = $results['summary']['convertedTotal'] + $financial['converted'];
            }
        }

        return $results;
    }
}
