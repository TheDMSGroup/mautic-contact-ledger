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

    const MAUTIC_CONTACT_LEDGER_STATUS_DECLINED  = 'rejected';

    const MAUTIC_CONTACT_LEDGER_STATUS_ENHANCED  = 'received';

    const MAUTIC_CONTACT_LEDGER_STATUS_RECEIVED  = 'received';

    const MAUTIC_CONTACT_LEDGER_STATUS_SCRUBBED  = 'received';

    /**
     * @param $dollarValue
     *
     * @return string
     */
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
    public function getCampaignRevenueData(Campaign $campaign, \DateTime $dateFrom, \DateTime $dateTo)
    {
        $results        = [];
        $resultDateTime = null;
        $results        = [];

        $sqlFrom = new \DateTime($dateFrom->format('Y-m-d'));
        $sqlFrom->modify('midnight');

        $sqlTo = new \DateTime($dateTo->format('Y-m-d'));
        $sqlTo->modify('midnight +1 day');

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
                $builder->expr()->gt('?', 'date_added')
            )
            ->groupBy('label')
            ->orderBy('label', 'ASC');

        $stmt = $this->getEntityManager()->getConnection()->prepare(
            $builder->getSQL()
        );

        // query the database
        $stmt->bindValue(1, $campaign->getId(), Type::INTEGER);
        $stmt->bindValue(2, $sqlFrom, Type::DATETIME);
        $stmt->bindValue(3, $sqlTo, Type::DATETIME);
        $stmt->execute();

        if (0 < $stmt->rowCount()) {
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        return $results;
    }

    /**
     * @param      $params
     * @param bool $bySource
     *
     * @return array
     */
    public function getDashboardRevenueWidgetData($params, $bySource = false)
    {
        $results = $financials = [];

        // get financials from ledger based on returned Lead list
        $f = $this->_em->getConnection()->createQueryBuilder();
        $f->select(
            "c.name, c.is_published, c.id as campaign_id, ss.contactsource_id, cs.name as source, SUM(cl.cost) as cost, SUM(cl.revenue) as revenue, SUM(ss.type IS NOT NULL) as received, SUM(ss.type NOT IN ('accepted', 'scrubbed')) as rejected, SUM(ss.type = 'accepted') as converted, SUM(ss.type = 'scrubbed') as scrubbed"
        )->from(MAUTIC_TABLE_PREFIX.'contact_ledger', 'cl');

        // join Contact Source Stats table to get type counts
        $f->join('cl', MAUTIC_TABLE_PREFIX.'contactsource_stats', 'ss', 'ss.contact_id = cl.contact_id');

        // join Campaign table to get name and publish status
        $f->join('cl', MAUTIC_TABLE_PREFIX.'campaigns', 'c', 'c.id = cl.campaign_id');

        // join Contact Source table to get source name
        $f->join('ss', MAUTIC_TABLE_PREFIX.'contactsource', 'cs', 'cs.id = ss.contactsource_id');

        //add optional date conditionals
        if ($params['dateFrom']) {
            $date = date_create($params['dateFrom']);
            date_sub($date, date_interval_create_from_date_string('1 days'));
            $params['dateFrom'] = date_format($date, 'Y-m-d');
            $f->where(
                $f->expr()->gte('ss.date_added', ':dateFrom')
            );
            $f->setParameter('dateFrom', $params['dateFrom']);
        }

        if ($params['dateTo']) {
            $date = date_create($params['dateTo']);
            date_add($date, date_interval_create_from_date_string('1 days'));
            $params['dateTo'] = date_format($date, 'Y-m-d');
            if (!$params['dateFrom']) {
                $f->where(
                    $f->expr()->lte('ss.date_added', ':dateTo')
                );
            } else {
                $f->andWhere(
                    $f->expr()->lte('ss.date_added', ':dateTo')
                );
            }
            $f->setParameter('dateTo', $params['dateTo']);
        }

        // either by Campaign, or by campaign & source
        if ($bySource) {
            $f->groupBy('cl.campaign_id, ss.contactsource_id');
        } else {
            $f->groupBy('cl.campaign_id');
        }

        $f->orderBy('COUNT(c.name)', 'ASC');

        if (isset($params['limit'])) {
            $f->setMaxResults($params['limit']);
        }

        $financials = $f->execute()->fetchAll();

        foreach ($financials as $financial) {
            $row=[];
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
            $result                 = [
                $financial['is_published'],
                $financial['campaign_id'],
                $financial['name'],
                $financial['contactsource_id'],
                $financial['source'],
                intval($financial['received']),
                intval($financial['scrubbed']),
                intval($financial['rejected']),
                intval($financial['converted']),
                $financial['revenue'],
                $financial['cost'],
                $financial['gm'],
                $financial['margin'],
                $financial['ecpm'],
            ];
            if (!$bySource) {
                unset($result[3], $result[4]);
                $result = array_values($result);
            }
            $results['rows'][] = $result;
        }

        return $results;
    }
}
