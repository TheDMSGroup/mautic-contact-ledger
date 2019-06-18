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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Connections\MasterSlaveConnection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
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
     * @param $params
     * @param true $
     * @param $cache_dir
     *
     * @return array
     */
    public function getDashboardClientWidgetData($params, $cache_dir = __DIR__, $groupBy = 'Client Name')
    {
        $results = [];
        $query   = $this->slaveQueryBuilder()
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
            ->leftJoin('ccs', MAUTIC_TABLE_PREFIX.'contactclient', 'cc', 'cc.id = ccs.contact_client_id')
            ->where('ccs.date_added BETWEEN :dateFrom AND :dateTo')
            ->groupBy('ccs.campaign_id')
            ->orderBy('c.name', 'ASC');

        if ('Client Category' == $groupBy) {
            $query->addSelect(
                'cat.title as category'
            );
            $query->leftJoin('ccs', MAUTIC_TABLE_PREFIX.'categories', 'cat', 'cc.category_id = cat.id');
            $query->addGroupBy('cat.title');
        } else {
            $query->addSelect(
                'ccs.contact_client_id as clientid,
                cc.name as clientname, 
                ccs.utm_source as utm_source'
            );
            $query->addGroupBy('ccs.contact_client_id', 'ccs.utm_source');
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
                $financial['rpu']          = number_format($financial['revenue'] / $financial['received'], 4, '.', ',');
            } else {
                $financial['gross_margin'] = 0;
                $financial['ecpm']         = 0;
                $financial['rpu']          = 0;
            }

            $result   = [
                $financial['active'],
                $financial['campaign_id'],
            ];
            $result[] = empty($financial['name']) ? '-' : $financial['name'];

            if ('Client Category' == $groupBy) {
                $result[] = (null == $financial['category']) ? '-' : $financial['category'];
            } else {
                $result[] = $financial['clientid'];
                $result[] = empty($financial['clientname']) ? '-' : $financial['clientname'];
                $result[] = empty($financial['utm_source']) ? '-' : $financial['utm_source'];
            }
            $result[] = $financial['received'];
            $result[] = $financial['declined'];
            $result[] = $financial['converted'];
            $result[] = number_format($financial['revenue'], 2, '.', ',');
            // hide the next 3 columns until cost is processed correctly
            // $result[] = number_format($financial['cost'], 2, '.', ',');
            // $result[] = number_format($financial['gross_income'], 2, '.', ',');
            // $result[] = $financial['gross_margin'];
            $result[] = $financial['ecpm'];
            $result[] = $financial['rpu'];

            $results['rows'][] = $result;
        }

        return $results;
    }

    /**
     * Create a DBAL QueryBuilder preferring a slave connection if available.
     *
     * @return QueryBuilder
     */
    private function slaveQueryBuilder()
    {
        /** @var Connection $connection */
        $connection = $this->getEntityManager()->getConnection();
        if ($connection instanceof MasterSlaveConnection) {
            $connection->connect('slave');
        }

        return new QueryBuilder($connection);
    }

    /**
     * @param $params
     * @param $cache_dir
     *
     * data for the client stats for a single campaign
     *
     * @return array
     */
    public function getCampaignClientTabData($params, $cache_dir = __DIR__)
    {
        $results = [];
        $query   = $this->slaveQueryBuilder()
            ->select(
                'ccs.contact_client_id as clientid,
                cc.name as clientname, 
                ccs.utm_source as utm_source,
                SUM(ccs.received) as received,
                SUM(ccs.declined) as declined,
                SUM(ccs.converted) as converted,
                SUM(ccs.revenue) as revenue,
                SUM(ccs.cost) as cost'
            )
            ->from(MAUTIC_TABLE_PREFIX.'contact_ledger_campaign_client_stats', 'ccs')
            ->leftJoin('ccs', MAUTIC_TABLE_PREFIX.'contactclient', 'cc', 'cc.id = ccs.contact_client_id')
            ->where('ccs.date_added BETWEEN :dateFrom AND :dateTo')
            ->andWhere('ccs.campaign_id =  :campaignId')
            ->groupBy('ccs.contact_client_id', 'ccs.utm_source')
            ->orderBy('cc.name', 'ASC');

        $query
            ->setParameter('dateFrom', $params['dateFrom'])
            ->setParameter('dateTo', $params['dateTo'])
            ->setParameter('campaignId', $params['campaignId']);

        $financials = $query->execute()->fetchAll();
        foreach ($financials as $financial) {
            $financial['gross_income'] = $financial['revenue'] - $financial['cost'];

            if ($financial['gross_income'] > 0) {
                $financial['gross_margin'] = 100 * $financial['gross_income'] / $financial['revenue'];
                $financial['ecpm']         = number_format($financial['gross_income'] / 1000, 4, '.', ',');
                $financial['rpu']          = number_format($financial['revenue'] / $financial['received'], 4, '.', ',');
            } else {
                $financial['gross_margin'] = 0;
                $financial['ecpm']         = 0;
                $financial['rpu']          = 0;
            }

            $result   = [$financial['clientid']];
            $result[] = empty($financial['clientname']) ? '-' : $financial['clientname'];
            $result[] = empty($financial['utm_source']) ? '-' : $financial['utm_source'];

            $result[] = $financial['received'];
            $result[] = $financial['declined'];
            $result[] = $financial['converted'];
            $result[] = number_format($financial['revenue'], 2, '.', ',');
            $result[] = $financial['ecpm'];
            $result[] = $financial['rpu'];

            $results['rows'][] = $result;
        }

        return $results;
    }

    /**
     * @param $params
     *
     * @return array
     */
    public function getExistingEntitiesByDate($params)
    {
        $criteria = ['dateAdded' => $params['dateTo']];

        $entities = $this->findBy($criteria);

        return $entities;
    }

    /**
     * @param               $params
     * @param EntityManager $em
     */
    public function updateExistingEntitiesByDate($params, EntityManager $em)
    {
        $now      = new \DateTime();
        $safeTime = $now->sub(new \DateInterval('PT15M'))->getTimestamp();

        if ($params['dateTo'] < $safeTime) {
            try {
                $qb = $em->getConnection()->createQueryBuilder();
                $qb->update($this->getTableName(), 's')
                    ->set('s.reprocess_flag', true)
                    ->where(
                        $qb->expr()->eq('s.reprocess_flag', 0),
                        $qb->expr()->eq('s.date_added', 'FROM_UNIXTIME(:dateAdded)')
                    )
                    ->setParameter('dateAdded', $params['dateTo'], Type::INTEGER);
                $em->getConnection()->getDatabasePlatform()->modifyLimitQuery($qb, 1);
                $qb->execute();
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * Gets MAX(date_added) Entity where reprocessFlag = 1.
     *
     * @return array
     */
    public function getMaxDateToReprocess()
    {
        $query  = $this->slaveQueryBuilder()
            ->select('date_added')
            ->from(MAUTIC_TABLE_PREFIX.'contact_ledger_campaign_client_stats', 'clccs')
            ->where('clccs.reprocess_flag = 1')
            ->setMaxResults(1)
            ->orderBy('date_added', 'DESC');
        $result = $query->execute()->fetchAll();

        return $result;
    }
}
