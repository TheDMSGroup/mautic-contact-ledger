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

use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Types\Type;
use Mautic\CampaignBundle\Entity\Campaign;
use Mautic\CoreBundle\Entity\CommonRepository;
use Mautic\CoreBundle\Helper\Chart\ChartQuery;

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
        $unit           = $this->getTimeUnitFromDateRange($dateFrom, $dateTo);

        $sqlFrom = new \DateTime($dateFrom->format('Y-m-d'));
        $sqlFrom->modify('midnight')->setTimeZone(new \DateTimeZone('UTC'));

        $sqlTo = new \DateTime($dateTo->format('Y-m-d'));
        $sqlTo->modify('midnight +1 day')->setTimeZone(new \DateTimeZone('UTC'));

        $builder          = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $chartQueryHelper = new ChartQuery($builder->getConnection(), $sqlFrom, $sqlTo, $unit);
        $dbunit           = $chartQueryHelper->translateTimeUnit($unit);
        $dbunit           = $dbunit == '%Y %U' ? '%Y week %u' : $dbunit;
        $dbunit           = $dbunit == '%Y-%m' ? '%M %Y' : $dbunit;

        $userTZ           = new \DateTime('now');
        $interval         = abs($userTZ->getOffset() / 3600);
        $selectExpr       = !in_array($unit, ['H', 'i', 's']) ?
            "DATE_FORMAT(date_added,  '$dbunit')           as label" :
            "DATE_FORMAT(DATE_SUB(date_added, INTERVAL $interval HOUR), '$dbunit')           as label";



        $builder
            ->select(
                $selectExpr,
                'SUM(IFNULL(cost, 0.0))                      as cost',
                'SUM(IFNULL(revenue, 0.0))                   as revenue',
                'SUM(IFNULL(revenue, 0.0))-SUM(IFNULL(cost, 0.0)) as profit'
            )
            ->from('contact_ledger')
            ->where(
                $builder->expr()->eq('?', 'campaign_id'),
                $builder->expr()->lte('?', 'date_added'),
                $builder->expr()->gt('?', 'date_added')
            );

        $builder->groupBy("DATE_FORMAT(date_added, '$dbunit')")

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

        // fix when only 1 result
        if(count($results)==1){
            $results = $this->fixSingleResultForCharts($results, $unit, $dbunit);
        }

        return $results;
    }

    /**
     * @param      $params
     * @param bool $bySource
     *
     * @return array
     */
    public function getDashboardRevenueWidgetData($params, $bySource = false, $cache_dir = __DIR__)
    {
        $statBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $statBuilder
            ->select(
                'ss.campaign_id',
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
            ->join('ss', MAUTIC_TABLE_PREFIX.'campaigns', 'c', 'c.id = ss.campaign_id')
            ->where('ss.date_added BETWEEN :dateFrom AND :dateTo')
            ->groupBy('ss.campaign_id, c.is_published, c.name, clc.cost, clr.revenue')
            ->orderBy('COUNT(ss.campaign_id)', 'ASC');
        $costBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $costBuilder
            ->select('lc.campaign_id', 'SUM(lc.cost) AS cost')
            ->from(MAUTIC_TABLE_PREFIX.'contact_ledger', 'lc')
            ->groupBy('lc.campaign_id');
        $costJoinCond = 'clc.campaign_id = ss.campaign_id';
        $revBuilder   = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $revBuilder
            ->select('lr.campaign_id', 'SUM(lr.revenue) AS revenue')
            ->from(MAUTIC_TABLE_PREFIX.'contact_ledger', 'lr')
            ->groupBy('lr.campaign_id');
        $revJoinCond = 'clr.campaign_id = ss.campaign_id';
        if ($bySource) {
            $statBuilder
                ->addSelect('ss.contactsource_id', 'cs.name as source')
                ->join('ss', MAUTIC_TABLE_PREFIX.'contactsource', 'cs', 'cs.id = ss.contactsource_id')
                ->addGroupBy('ss.contactsource_id, cs.name');
            $costBuilder
                ->addSelect('sc.contactsource_id')
                ->innerJoin(
                    'lc',
                    'contactsource_stats',
                    'sc',
                    'lc.campaign_id = sc.campaign_id AND lc.contact_id = sc.contact_id'
                )
                ->addGroupBy('sc.contactsource_id');
            $costJoinCond .= ' AND clc.contactsource_id = ss.contactsource_id';
            $revBuilder
                ->addSelect('sr.contactsource_id')
                ->innerJoin(
                    'lr',
                    'contactsource_stats',
                    'sr',
                    'lr.campaign_id = sr.campaign_id AND lr.contact_id = sr.contact_id'
                )
                ->addGroupBy('sr.contactsource_id');
            $revJoinCond .= ' AND clr.contactsource_id = ss.contactsource_id';
        }
        $statBuilder
            ->leftJoin('ss', '('.$costBuilder->getSQL().')', 'clc', $costJoinCond)
            ->leftJoin('ss', '('.$revBuilder->getSQL().')', 'clr', $revJoinCond);
        $statBuilder
            ->setParameter('dateFrom', $params['dateFrom'])
            ->setParameter('dateTo', $params['dateTo']);
        if (isset($params['limit']) && (0 < $params['limit'])) {
            $statBuilder->setMaxResults($params['limit']);
        }
        $results = ['rows' => []];

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
        foreach ($financials as $financial) {
            // must be ordered as active, id, name, received, converted, revenue, cost, gm, margin, ecpm
            $financial['revenue']      = number_format(floatval($financial['revenue']), 2, '.', ',');
            $financial['cost']         = number_format(floatval($financial['cost']), 2, '.', ',');
            $financial['gross_income'] = number_format($financial['revenue'] - $financial['cost'], 2, '.', ',');

            if ($financial['gross_income'] > 0) {
                $financial['gross_margin'] = number_format(100 * $financial['gross_income'] / $financial['revenue'], 0, '.', ',');
                $financial['ecpm']         = number_format($financial['gross_income'] / 1000, 4, '.', ',');
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
        }

        return $results;
    }

    /**
     * Returns appropriate time unit from a date range so the line/bar charts won't be too full/empty.
     *
     * @param $dateFrom
     * @param $dateTo
     *
     * @return string
     */
    public function getTimeUnitFromDateRange($dateFrom, $dateTo)
    {
        $dayDiff = $dateTo->diff($dateFrom)->format('%a');
        $unit    = 'd';

        if ($dayDiff <= 1) {
            $unit = 'H';

            $sameDay    = $dateTo->format('d') == $dateFrom->format('d') ? 1 : 0;
            $hourDiff   = $dateTo->diff($dateFrom)->format('%h');
            $minuteDiff = $dateTo->diff($dateFrom)->format('%i');
            if ($sameDay && !intval($hourDiff) && intval($minuteDiff)) {
                $unit = 'i';
            }
            $secondDiff = $dateTo->diff($dateFrom)->format('%s');
            if (!intval($minuteDiff) && intval($secondDiff)) {
                $unit = 'i';
            }
        }
        if ($dayDiff > 31) {
            $unit = 'W';
        }
        if ($dayDiff > 100) {
            $unit = 'm';
        }
        if ($dayDiff > 1000) {
            $unit = 'Y';
        }

        return $unit;
    }

    protected function fixSingleResultForCharts($results, $unit, $dbunit)
    {
        $unitStrings = [
            'H' => '1 Hour',
            'W' => '1 Week',
            'D' => '1 Day',
            'm' => '1 Month',
            'i' => '1 Minute',
            's' => '1 Second',
            'Y' => '1 Year'
        ];

        $unitBefore = date_sub(new \DateTime($results[0]['label']), date_interval_create_from_date_string($unitStrings[$unit]));
        $unitAfter = date_add(new \DateTime($results[0]['label']), date_interval_create_from_date_string($unitStrings[$unit]));
        array_unshift($results,[
            'cost' => "0",
            'label' => $unitBefore->format(str_replace('%', '', $dbunit)),
            'profit' => "0",
            'revenue' => "0"
        ]);
        array_push($results,[
            'cost' => "0",
            'label' => $unitAfter->format(str_replace('%', '', $dbunit)),
            'profit' => "0",
            'revenue' => "0"
        ]);

        return $results;
    }
}
