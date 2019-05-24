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

use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Mautic\CampaignBundle\Entity\Campaign;
use Mautic\CoreBundle\Entity\CommonRepository;
use PDO;

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
     * @param           $unit
     * @param           $dbunit
     * @param string    $cache_dir
     * @param string    $dateFormat
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\Cache\CacheException
     */
    public function getCampaignRevenueData(
        Campaign $campaign,
        \DateTime $dateFrom,
        \DateTime $dateTo,
        $unit,
        $dbunit,
        $cache_dir = __DIR__
    ) {
        $builder = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $builder
            ->select(
                'DATE_FORMAT(DATE_ADD(date_added, INTERVAL :interval SECOND), :dbUnit) as label',
                'SUM(IFNULL(cost, 0.0))                                                as cost',
                'SUM(IFNULL(revenue, 0.0))                                             as revenue',
                'SUM(IFNULL(revenue, 0.0))-SUM(IFNULL(cost, 0.0))                      as profit'
            )
            ->from('contact_ledger')
            ->where(
                $builder->expr()->eq('campaign_id', ':campaignId'),
                $builder->expr()->gte('date_added', 'FROM_UNIXTIME(:dateFrom)'),
                $builder->expr()->lte('date_added', 'FROM_UNIXTIME(:dateTo)')
            )
            ->groupBy('label');

        // query the database
        $builder->setParameter(':interval', (new \DateTime())->getOffset(), Type::INTEGER);
        $builder->setParameter(':dbUnit', $dbunit, Type::STRING);
        $builder->setParameter(':campaignId', $campaign->getId(), Type::INTEGER);
        $builder->setParameter(':dateFrom', $dateFrom->getTimestamp(), Type::INTEGER);
        $builder->setParameter(':dateTo', $dateTo->getTimestamp(), Type::INTEGER);

        $cache = new FilesystemCache($cache_dir.'/sql');

        $stmt = $builder->getConnection()->executeCacheQuery(
            $builder->getSQL(),
            $builder->getParameters(),
            $builder->getParameterTypes(),
            new QueryCacheProfile(900, 'campaign-revenue-queries', $cache)
        );

        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt->closeCursor();

        // Sort and pad-out the results to match the other charts.
        $intervalMap = [
            'H' => ['hour', 'Y-m-d H:00'],
            'd' => ['day', 'Y-m-d'],
            'W' => ['week', 'Y \w\e\e\k W'],
            'Y' => ['year', 'Y'],
            'm' => ['minute', 'Y-m-d H:i'],
            's' => ['second', 'Y-m-d H:i:s'],
        ];
        $interval    = \DateInterval::createFromDateString('1 '.$intervalMap[$unit][0]);
        $periods     = new \DatePeriod($dateFrom, $interval, $dateTo);
        $updatedData = [];
        foreach ($periods as $period) {
            $dateToCheck   = $period->format($intervalMap[$unit][1]);
            $dataKey       = array_search($dateToCheck, array_column($data, 'label'));
            $updatedData[] = [
                'label'   => $dateToCheck,
                'cost'    => (false !== $dataKey) ? $data[$dataKey]['cost'] : 0,
                'revenue' => (false !== $dataKey) ? $data[$dataKey]['revenue'] : 0,
                'profit'  => (false !== $dataKey) ? $data[$dataKey]['profit'] : 0,
            ];
        }

        return $updatedData;
    }

    /**
     * @param        $params
     * @param bool   $bySource
     * @param string $cache_dir
     * @param bool   $realtime
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\Cache\CacheException
     */
    public function getCampaignSourceStatsData($params, $bySource = false, $cache_dir = __DIR__, $realtime = true)
    {
        $statBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $statBuilder
            ->select(
                'ss.campaign_id',
                'ss.date_added',
                'c.is_published',
                'c.name',
                'SUM(IF(ss.type IS NULL                     , 0, 1)) AS received',
                "SUM(IF(ss.type IN ('accepted' , 'scrubbed'), 0, 1)) AS rejected",
                "SUM(IF(ss.type = 'accepted'                , 1, 0)) AS converted",
                "SUM(IF(ss.type = 'scrubbed'                , 1, 0)) AS scrubbed",
                'IFNULL(clc.cost, 0)                                 AS cost',
                'IFNULL(clr.revenue, 0)                              AS revenue'
            )
            ->from(MAUTIC_TABLE_PREFIX.'contactsource_stats', 'ss')
            ->join('ss', MAUTIC_TABLE_PREFIX.'leads', 'l', 'l.id = ss.contact_id')
            ->join('ss', MAUTIC_TABLE_PREFIX.'campaigns', 'c', 'c.id = ss.campaign_id')
            ->where('ss.type <> :invalid AND l.date_identified BETWEEN :dateFrom AND :dateTo')
            ->groupBy('ss.campaign_id')
            ->orderBy('COUNT(ss.campaign_id)', 'ASC');
        $costBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $costBuilder
            ->select('lc.campaign_id', 'SUM(lc.cost) AS cost')
            ->from(MAUTIC_TABLE_PREFIX.'contact_ledger', 'lc')
            ->groupBy('lc.campaign_id');
        $costJoinCond = 'clc.campaign_id = ss.campaign_id AND ss.date_added BETWEEN :dateFrom AND :dateTo';
        $revBuilder   = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $revBuilder
            ->select('lr.campaign_id', 'SUM(lr.revenue) AS revenue')
            ->from(MAUTIC_TABLE_PREFIX.'contact_ledger', 'lr')
            ->groupBy('lr.campaign_id');
        $revJoinCond = 'clr.campaign_id = ss.campaign_id AND ss.date_added BETWEEN :dateFrom AND :dateTo';
        if ($bySource) {
            $statBuilder
                ->addSelect('ss.contactsource_id', 'cs.name as source', 'cs.utm_source as utm_source')
                ->join('ss', MAUTIC_TABLE_PREFIX.'contactsource', 'cs', 'cs.id = ss.contactsource_id')
                ->addGroupBy('ss.contactsource_id, cs.utm_source');
        }
        $costBuilder
            ->addSelect('sc.contactsource_id')
            ->innerJoin(
                'lc',
                'contactsource_stats',
                'sc',
                'lc.campaign_id = sc.campaign_id AND lc.contact_id = sc.contact_id AND sc.date_added BETWEEN :dateFrom AND :dateTo'
            )
            ->addGroupBy('sc.contactsource_id');
        $costJoinCond .= ' AND clc.contactsource_id = ss.contactsource_id';
        $revBuilder
            ->addSelect('sr.contactsource_id')
            ->innerJoin(
                'lr',
                'contactsource_stats',
                'sr',
                'lr.campaign_id = sr.campaign_id AND lr.contact_id = sr.contact_id AND sr.date_added BETWEEN :dateFrom AND :dateTo'
            )
            ->addGroupBy('sr.contactsource_id');
        $revJoinCond .= ' AND clr.contactsource_id = ss.contactsource_id';

        $statBuilder
            ->leftJoin('ss', '('.$costBuilder->getSQL().')', 'clc', $costJoinCond)
            ->leftJoin('ss', '('.$revBuilder->getSQL().')', 'clr', $revJoinCond);
        $statBuilder
            ->setParameter('invalid', 'invalid')
            ->setParameter('dateFrom', $params['dateFrom'])
            ->setParameter('dateTo', $params['dateTo']);
        if (isset($params['limit']) && (0 < $params['limit'])) {
            $statBuilder->setMaxResults($params['limit']);
        }
        $results         = ['rows' => []];
        $resultsWithKeys = [];

        // setup cache
        $cache = new FilesystemCache($cache_dir.'/sql');
        $statBuilder->getConnection()->getConfiguration()->setResultCacheImpl($cache);
        $stmt       = $statBuilder->getConnection()->executeCacheQuery(
            $statBuilder->getSQL(),
            $statBuilder->getParameters(),
            $statBuilder->getParameterTypes(),
            new QueryCacheProfile(900, 'dashboard-revenue-queries', $cache)
        );
        $financials = $stmt->fetchAll();
        $stmt->closeCursor();

        // get utm_source data for leads with NO Contact Source Stat record (direct API, manually created, imports etc)
        $otherFinancials = $this->getAlternateCampaignSourceData($params, $bySource, $cache_dir, $realtime);
        $financials      = array_merge($financials, $otherFinancials);

        if (empty($financials)) {
            // no data for the time period, so return empty row values
            $financials[0] = [
                'is_published' => 0,
                'campaign_id'  => null,
                'name'         => null,
                'received'     => 0,
                'scrubbed'     => 0,
                'rejected'     => 0,
                'converted'    => 0,
                'revenue'      => 0,
                'cost'         => 0,
            ];
            if ($bySource) {
                $financials[0]['contactsource_id'] = null;
                $financials[0]['source']           = null;
                $financials[0]['utm_source']       = ''; // cant be null
            }
        }

        foreach ($financials as $financial) {
            // must be ordered as active, id, name, received, converted, revenue, cost, gm, margin, ecpm
            $financial['revenue']      = number_format(floatval($financial['revenue']), 2, '.', ',');
            $financial['cost']         = number_format(floatval($financial['cost']), 2, '.', ',');
            $financial['gross_income'] = number_format(
                (float) $financial['revenue'] - (float) $financial['cost'],
                2,
                '.',
                ','
            );

            if ($financial['gross_income'] > 0) {
                $financial['gross_margin'] = number_format(
                    100 * $financial['gross_income'] / $financial['revenue'],
                    0,
                    '.',
                    ','
                );
                $financial['ecpm']         = number_format((float) $financial['gross_income'] / 1000, 4, '.', ',');
            } else {
                $financial['gross_margin'] = 0;
                $financial['ecpm']         = 0;
            }

            $result = [
                $financial['is_published'],
                $financial['campaign_id'],
                $financial['name'],
            ];
            if ($bySource) {
                $result[] = $financial['contactsource_id'];
                $result[] = $financial['source'];
                $result[] = $financial['utm_source'];
            }
            $result[] = $financial['received'];
            $result[] = $financial['scrubbed'];
            $result[] = $financial['rejected'];
            $result[] = $financial['converted'];
            $result[] = $financial['revenue'];
            $result[] = $financial['cost'];
            $result[] = $financial['gross_income'];
            $result[] = $financial['gross_margin'];
            $result[] = $financial['ecpm'];

            $results['rows'][] = $result;
            $resultsWithKeys[] = $financial;
        }

        return true == $realtime ? $results : $resultsWithKeys;
    }

    /**
     * @param        $params
     * @param bool   $bySource
     * @param string $cache_dir
     * @param bool   $realtime
     *
     * @return array
     */
    public function getAlternateCampaignSourceData($params, $bySource = false, $cache_dir = __DIR__, $realtime = true)
    {
        // get array of leads first.
        $leadBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $leadBuilder
            ->select(
                'l1.id'
            )
            ->from(MAUTIC_TABLE_PREFIX.'leads', 'l1')
            ->leftJoin('l1', MAUTIC_TABLE_PREFIX.'contactsource_stats', 'cs', 'l1.id = cs.contact_id')
            ->where('cs.contact_id IS NULL')
            ->andWhere('l1.date_identified BETWEEN :dateFrom AND :dateTo');
        $leadBuilder
            ->setParameter('dateFrom', $params['dateFrom'])
            ->setParameter('dateTo', $params['dateTo']);
        $leads = $leadBuilder->execute()->fetchAll(PDO::FETCH_COLUMN);

        // Main Query
        $ledgerBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $ledgerBuilder
            ->select(
                'COUNT(l.id) AS received',
                'SUM(IF(cc.type = "converted", 1, 0)) AS converted',
                'SUM(cl.`cost`) AS cost',
                'SUM(cl.`revenue`) AS revenue',
                'cal.campaign_id',
                'c.name',
                'c.is_published',
                '0 as scrubbed',
                '0 as rejected',
                'NULL as contactsource_id',
                'NULL as source',
                'cc.date_added'
            )
            ->from('('.$leadBuilder->getSQL().')', 'l');

        // cost and revenue expression
        $costRevenueBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $costRevenueBuilder
            ->select('SUM(clss.cost) as cost', 'SUM(clss.revenue) as revenue', 'clss.campaign_id', 'clss.contact_id')
            ->from(MAUTIC_TABLE_PREFIX.'contact_ledger', 'clss')
            ->where('clss.contact_id IN (:leads)')
            //->andWhere('AND clss.class_name = "ContactClient"')
            ->groupBy('clss.contact_id', 'clss.campaign_id');

        // converted expression
        $convertedBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $convertedBuilder
            ->select('ccss.type', 'ccss.contact_id', 'ccss.date_added')
            ->from(MAUTIC_TABLE_PREFIX.'contactclient_stats', 'ccss')
            ->where('ccss.contact_id IN (:leads)')
            ->groupBy('ccss.contact_id');

        $ledgerBuilder
            ->leftJoin('l', '('.$costRevenueBuilder->getSQL().')', 'cl', 'l.id = cl.contact_id')
            ->leftJoin('l', '('.$convertedBuilder->getSQL().')', 'cc', 'l.id = cc.contact_id')
            ->leftJoin('l', 'campaign_leads', 'cal', 'cal.lead_id = l.id')
            ->leftJoin('cal', 'campaigns', 'c', 'cal.campaign_id = c.id');

        $ledgerBuilder
            ->groupBy('cl.campaign_id');

        if ($bySource) {
            $ledgerBuilder
                ->addSelect('lu.utm_source')
                ->leftJoin('l', MAUTIC_TABLE_PREFIX.'lead_utmtags', 'lu', 'l.id = lu.lead_id')
                ->addGroupBy('lu.utm_source');
        }
        $ledgerBuilder
            ->setParameter('leads', $leads, Connection::PARAM_STR_ARRAY)
            ->setParameter('dateFrom', $params['dateFrom'])
            ->setParameter('dateTo', $params['dateTo']);

        $ledger = $ledgerBuilder->execute()->fetchAll();

        return $ledger;
    }

    /**
     * @param        $params
     * @param string $cache_dir
     * @param bool   $realtime
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\Cache\CacheException
     */
    public function getCampaignClientStatsData($params, $cache_dir = __DIR__, $realtime = true)
    {
        // get list of campaigns Ids. The indexes on contactclient_stats table require a where clause with campaign_id, so get them all.
        $campaignBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $campaignBuilder->select('camp.id')
                        ->from(MAUTIC_TABLE_PREFIX.'campaigns', 'camp');
        $campaignIds = $campaignBuilder->execute()->fetchAll(PDO::FETCH_COLUMN);

        // OK now query the contactclient_stats table using the campaign Ids and dates from params
        $clientstatBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $clientstatBuilder
            ->select(
                'COUNT(cc.contact_id ) AS received',
                'SUM(IF(cc.type = "rejected", 1, 0)) AS rejected',
                'SUM(IF(cc.type = "converted", 1, 0)) AS converted',
                'SUM(cc.attribution) AS revenue',
                'cc.campaign_id',
                'cc.utm_source AS utm_source',
                'c.is_published',
                'c.name as campaign_name',
                'cc.contactclient_id as contactclient_id',
                '0 as cost'
            )
            ->from(MAUTIC_TABLE_PREFIX.'contactclient_stats', 'cc')
            ->where('cc.campaign_id IN(:campaignIds)')
            ->andWhere('cc.date_added BETWEEN :dateFrom AND :dateTo')
            ->groupBy('cc.campaign_id', 'cc.contactclient_id', 'cc.utm_source')
            ->setParameter('campaignIds', $campaignIds, Connection::PARAM_INT_ARRAY)
            ->setParameter('dateFrom', $params['dateFrom'])
            ->setParameter('dateTo', $params['dateTo'])
            ->leftJoin('cc', MAUTIC_TABLE_PREFIX.'campaigns', 'c', 'cc.campaign_id = c.id');

        if (isset($params['limit']) && (0 < $params['limit'])) {
            $clientstatBuilder->setMaxResults($params['limit']);
        }
        $results         = ['rows' => []];
        $resultsWithKeys = [];

        // setup cache
        $cache = new FilesystemCache($cache_dir.'/sql');
        $clientstatBuilder->getConnection()->getConfiguration()->setResultCacheImpl($cache);
        $stmt       = $clientstatBuilder->getConnection()->executeCacheQuery(
            $clientstatBuilder->getSQL(),
            $clientstatBuilder->getParameters(),
            $clientstatBuilder->getParameterTypes(),
            new QueryCacheProfile(900, 'dashboard-revenue-queries', $cache)
        );

        $financials = $stmt->fetchAll();
        $stmt->closeCursor();

        if (empty($financials)) {
            // no data for the time period, so return empty row values
            $financials[0] = [
                'is_published'     => 0,
                'campaign_id'      => null,
                'campaign_name'    => null,
                'contactclient_id' => null,
                'utm_source'       => '', // cant be null
                'received'         => 0,
                'rejected'         => 0,
                'converted'        => 0,
                'revenue'          => 0,
                'cost'             => 0,
            ];
        }

        foreach ($financials as $financial) {
            $financial['revenue']      = number_format(floatval($financial['revenue']), 2, '.', ',');
            $financial['gross_income'] = number_format(
                (float) $financial['revenue'] - (float) $financial['cost'],
                2,
                '.',
                ','
            );

            if ($financial['gross_income'] > 0) {
                $financial['gross_margin'] = number_format(
                    100 * $financial['gross_income'] / $financial['revenue'],
                    0,
                    '.',
                    ','
                );
                $financial['ecpm']         = number_format((float) $financial['gross_income'] / 1000, 4, '.', ',');
            } else {
                $financial['gross_margin'] = 0;
                $financial['ecpm']         = 0;
            }

            $result = [
                $financial['is_published'],
                $financial['campaign_id'],
                $financial['campaign_name'],
                $financial['contactclient_id'],
                $financial['utm_source'],
                $financial['received'],
                $financial['rejected'],
                $financial['converted'],
                $financial['revenue'],
                $financial['cost'],
                $financial['gross_income'],
                $financial['gross_margin'],
                $financial['ecpm'],
            ];

            $results['rows'][] = $result;
            $resultsWithKeys[] = $financial;
        }

        return true == $realtime ? $results : $resultsWithKeys;
    }
}
