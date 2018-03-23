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
use Symfony\Component\Validator\Constraints\DateTime;

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

        $query = $builder->getSQL();
        $params = [
            'id' => $campaign->getId(),
            'from' => $dateFrom,
            'to' => $dateTo
        ];

        try {
            $results = $this->getEntityManager()->getConnection()->fetchAll($query, $params);
        } catch (\Exception $e) {
            die($e->getFile() . $e->getLine() . $e->getMessage());
        }

        $labels = $costs = $revenues = $profits = [];

        foreach ($results as $result) {
            list($costs[], $revenues[], $profites[], $labels[]) = $result;
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
