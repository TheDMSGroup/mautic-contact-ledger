<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Digital Media Solutions, LLC
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactLedgerBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;

/**
 * Class CampaignClientStatsRepository.
 */
class CampaignClientStatsRepository extends CommonRepository
{
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
        return 'ccs';
    }

    /**
     * Gets the ID of the latest ID.
     *
     * @return int
     */
    public function getMaxId()
    {
        $result = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select('max(id) AS id')
            ->from(MAUTIC_TABLE_PREFIX.'contact_ledger_campaign_client_stats', 'ccs')
            ->execute()->fetchAll();

        return $result[0]['id'];
    }

    /**
     * Gets the ID of the latest ID.
     *
     * @return object
     */
    public function getLastEntity()
    {
        $entity = null;
        $result = $this->getMaxId();

        if (isset($result)) {
            $entity = $this->getEntity($result);
        }

        return $entity;
    }

    /**
     * @param $params
     * @param true $
     * @param $cache_dir
     *
     * @return array
     */
    public function getDashboardRevenueWidgetData($params, $bySource = false, $cache_dir = __DIR__, $groupBy = 'Client Name')
    {
        $results = [];
        $query   = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select(
                'c.is_published as active,
                ccs.campaign_id,
            c.name as name,
            SUM(ccs.received) as received,
            SUM(ccs.declined) as declined,
            SUM(ccs.converted) as converted,
            SUM(ccs.revenue) as revenue,
            SUM(ccs.cost) as cost'
            )
            ->from(MAUTIC_TABLE_PREFIX.'contact_ledger_campaign_client_stats', 'ccs')
            ->join('ccs', MAUTIC_TABLE_PREFIX.'campaigns', 'c', 'c.id = ccs.campaign_id')
            ->leftJoin('ccs', MAUTIC_TABLE_PREFIX.'contactclient', 'cc', 'cc.id = ccs.contact_source_id')
            ->where('ccs.date_added BETWEEN :dateFrom AND :dateTo')
            ->groupBy('ccs.campaign_id')
            ->orderBy('c.name', 'ASC');

        if ($bySource) {
            if ('Source Category' == $groupBy) {
                $query->addSelect(
                    'cat.title as category'
                );
                $query->leftJoin('cs', MAUTIC_TABLE_PREFIX.'categories', 'cat', 'cs.category_id = cat.id');
                $query->addGroupBy('cat.title');
            } else {
                $query->addSelect(
                'ccs.contact_client_id as clientid,
                cc.name as clientname, 
                ccs.utm_source as utm_source'
                );
                $query->addGroupBy('ccs.contact_client_id', 'ccs.utm_source');
            }
        }
        $query
            ->setParameter('dateFrom', $params['dateFrom'])
            ->setParameter('dateTo', $params['dateTo']);

        $financials = $query->execute()->fetchAll();
        foreach ($financials as $financial) {
            $financial['gross_income'] = $financial['revenue'] - $financial['cost'];

            if ($financial['gross_income'] > 0) {
                $financial['gross_margin'] = 100 * $financial['gross_income'] / $financial['revenue'];
                $financial['ecpm']         = number_format($financial['gross_income'] / 1000, 4, '.', ',');
            } else {
                $financial['gross_margin'] = 0;
                $financial['ecpm']         = 0;
            }

            $result   = [
                $financial['active'],
                $financial['campaign_id'],
            ];
            $result[] = empty($financial['name']) ? '-' : $financial['name'];

            if ($bySource) {
                if ('Source Category' == $groupBy) {
                    $result[] = (null == $financial['category']) ? '-' : $financial['category'];
                } else {
                    $result[] = $financial['clientid'];
                    $result[] = empty($financial['clientname']) ? '-' : $financial['clientname'];
                    $result[] = empty($financial['utm_source']) ? '-' : $financial['utm_source'];
                }
            }
            $result[] = $financial['received'];
            $result[] = $financial['declined'];
            $result[] = $financial['converted'];
            $result[] = number_format($financial['revenue'], 2, '.', ',');
            $result[] = number_format($financial['cost'], 2, '.', ',');
            $result[] = number_format($financial['gross_income'], 2, '.', ',');
            $result[] = $financial['gross_margin'];
            $result[] = $financial['ecpm'];

            $results['rows'][] = $result;
        }

        return $results;
    }

    /**
     * Gets MAX(date_added) Entity where reprocessFlag = 1.
     *
     * @return object
     */
    public function getMaxDateToReprocess()
    {
        $query   = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select('MAX(clccs.id)')
            ->from(MAUTIC_TABLE_PREFIX.'contact_ledger_campaign_client_stats', 'clccs')
            ->where('clccs.reprocess_flag = 1');
        $result = $query->execute()->fetchAll();

        if (isset($result)) {
            $entity = $this->getEntity($result[0]['MAX(clccs.id)']);
        }

        return $entity;
    }

    public function getEntitiesToReprocess($params)
    {
        $criteria = ['dateAdded'=>$params['dateTo'], 'reprocessFlag' => true];

        $entities = $this->findBy($criteria);

        return $entities;
    }
}
