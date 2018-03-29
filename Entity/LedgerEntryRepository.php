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

use Doctrine\DBAL\Types\Type;
use Mautic\CampaignBundle\Entity\Campaign;
use Mautic\CoreBundle\Entity\CommonRepository;

/**
 * Class LedgerEntryRepository.
 */
class LedgerEntryRepository extends CommonRepository
{
    const MAUTIC_CONTACT_LEDGER_STATUS_CONVERTED = 'converted';
    const MAUTIC_CONTACT_LEDGER_STATUS_RECEIVED  = 'received';
    const MAUTIC_CONTACT_LEDGER_STATUS_ENHANCED  = 'received';
    const MAUTIC_CONTACT_LEDGER_STATUS_SCRUBBED  = 'received';

    public static function formatDollar($dollarValue)
    {
        return sprintf('%19.4f', floatval($dollarValue));
    }

    /**
     * @return string
     */
    public function getTableAlias()
    {
        return 'cle';
    }

    /**
     * @param Campaign  $campaign
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     *
     * @return array
     */
    public function getForRevenueChartData(Campaign $campaign, \DateTime $dateFrom, \DateTime $dateTo)
    {
        $resultDateTime = null;
        $labels         = $costs         = $revenues         = $profits         = [];
        $defaultDollars = self::formatDollar('0');

        $builder = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $builder
            ->select(
                'DATE_FORMAT(date_added, "%Y%m%d")           as label',
                'SUM(IFNULL(cost, 0.0))                      as cost',
                'SUM(IFNULL(revenue, 0.0))                   as revenue',
                'SUM(IFNULL(revenue, 0.0))-SUM(IFNULL(cost, 0.0)) as profit'
            )
            ->from('contact_ledger')
            ->where(
                $builder->expr()->eq('?', 'campaign_id'),
                $builder->expr()->lte('?', 'date_added'),
                $builder->expr()->gte('?', 'date_added')
            )
            ->groupBy('label')
            ->orderBy('label', 'ASC');

        $stmt = $this->getEntityManager()->getConnection()->prepare(
            $builder->getSQL()
        );

        // query the database
        $stmt->bindValue(1, $campaign->getId(), Type::INTEGER);
        $stmt->bindValue(2, $dateFrom, Type::DATETIME);
        $stmt->bindValue(3, $dateTo, Type::DATETIME);
        $stmt->execute();

        if (0 < $stmt->rowCount()) {
            $results        = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $result         = array_shift($results);
            $resultDateTime = new \DateTime($result['label']);
        }

        // iterate over range steps
        $labelDateTime = new \DateTime($dateFrom->format('Ymd'));
        while ($dateTo >= $labelDateTime) {
            $labels[] = $labelDateTime->format('M j, y');

            if ($labelDateTime == $resultDateTime) {
                // record match
                $costs[]    = self::formatDollar(-$result['cost']);
                $revenues[] = self::formatDollar($result['revenue']);
                $profits[]  = self::formatDollar($result['profit']);

                // prep next entry
                if (0 < count($results)) {
                    $result         = array_shift($results);
                    $resultDateTime = new \DateTime($result['label']);
                }
            } else {
                $costs[]    = $defaultDollars;
                $revenues[] = $defaultDollars;
                $profits[]  = $defaultDollars;
            }

            $labelDateTime->modify('+1 day');
        }

        //undo change for inclusive filters
        $dateTo->modify('-1 second');

        return [
            'labels'   => $labels,
            'datasets' => [
                [
                    'label'                     => 'Cost',
                    'data'                      => $costs,
                    'backgroundColor'           => 'rgba(204,51,51,0.1)',
                    'borderColor'               => 'rgba(204,51,51,0.8)',
                    'pointHoverBackgroundColor' => 'rgba(204,51,51,0.75)',
                    'pointHoverBorderColor'     => 'rgba(204,51,51,1)',
                ],
                [
                    'label'                     => 'Reveue',
                    'data'                      => $revenues,
                    'backgroundColor'           => 'rgba(51,51,51,0.1)',
                    'borderColor'               => 'rgba(51,51,51,0.8)',
                    'pointHoverBackgroundColor' => 'rgba(51,51,51,0.75)',
                    'pointHoverBorderColor'     => 'rgba(51,51,51,1)',
                ],
                [
                    'label'                     => 'Profit',
                    'data'                      => $profits,
                    'backgroundColor'           => 'rgba(51,204,51,0.1)',
                    'borderColor'               => 'rgba(51,204,51,0.8)',
                    'pointHoverBackgroundColor' => 'rgba(51,204,51,0.75)',
                    'pointHoverBorderColor'     => 'rgba(51,204,51,1)',
                ],
            ],
        ];
    }

    /**
     * @param $params
     *
     * @return array
     */
    public function getDashboardRevenueWidgetData($params)
    {
        $results = $financials = [];

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
            $c->setParameter('MAUTIC_CONVERSION_LABEL', self::MAUTIC_CONTACT_LEDGER_STATUS_CONVERTED);

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

    public function getDashboardSourceRevenueWidgetData($params)
    {
        $results = $financials = [];

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
                'c.name as campaign_name, c.is_published, c.id as campaign_id, SUM(cl.cost) as cost, SUM(cl.revenue) as revenue, COUNT(cl.contact_id) as received, cs.id as source_id, cs.name as source_name'
            )->from(MAUTIC_TABLE_PREFIX.'contact_ledger', 'cl');

            $f->groupBy('cl.campaign_id');

            // join Campaign table to get name and publish status
            $f->join('cl', MAUTIC_TABLE_PREFIX.'campaigns', 'c', 'c.id = cl.campaign_id');

            // join Integration table to get contact source from lead mapping
            $f->join('cl',
                MAUTIC_TABLE_PREFIX.'integration_entity',
                'i',
                'cl.contact_id = i.internal_entity_id AND i.internal_entity = :lead AND i.integration_entity = :ContactClientSource');
            $f->setParameter('ContactClientSource', 'ContactSource');
            $f->setParameter('lead', 'lead');

            // join Contact Source table to get contact source name
            $f->join('cl', MAUTIC_TABLE_PREFIX.'contactsource', 'cs', 'i.internal_entity_id = cs.id');

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
            $c->setParameter('MAUTIC_CONVERSION_LABEL', self::MAUTIC_CONTACT_LEDGER_STATUS_CONVERTED);

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
                    $financial['campaign_name'],
                    $financial['source_id'],
                    $financial['source_name'],
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
