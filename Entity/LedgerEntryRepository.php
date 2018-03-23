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

use DateTime;
use Mautic\CampaignBundle\Entity\Campaign;
use Mautic\CoreBundle\Entity\CommonRepository;

/**
 * Class LedgerEntryRepository
 */
class LedgerEntryRepository extends CommonRepository
{
    public function getTableAlias()
    {
        return 'cle';
    }

    /**
     * @param Campaign $campaign
     *
     * @return array
     */
    public function getCampaignChartData(Campaign $campaign, DateTime $dateFrom, DateTime $dateTo)
    {
        $builder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $builder
            ->select(
                'DATE_FORMAT(date_added, "%b %e, %y") as label',
                'SUM(cost) as cost',
                'SUM(revenue) as revenue',
                'SUM(revenue)-SUM(cost) as profit'
            )
            ->from('contact_ledger')
            ->where(
                'campaign_id = '.$campaign->getId(),
                'date_added BETWEEN "'.$dateFrom->format('Y-m-d').'" AND "'.$dateTo->format('Y-m-d').'"'
            )
            ->groupBy('label')
            ->orderBy('label', 'ASC');

        $query = $builder->getSQL();

        $results = $this->getEntityManager()->getConnection()->fetchAll($query);

        $labels = $costs = $revenues = $profits = [];

        foreach ($results as $result) {
            $labels[] = $result['label'];
            $costs[] = -$result['cost'];
            $revenues[] = $result['revenue'];
            $profits[] = $result['profit'];
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Cost',
                    'data' => $costs,
                ],
                [
                    'label' => 'Reveue',
                    'data' => $revenues,
                ],
                [
                    'label' => 'Profit',
                    'data' => $profits,
                ],
            ]
        ];
    }
}
