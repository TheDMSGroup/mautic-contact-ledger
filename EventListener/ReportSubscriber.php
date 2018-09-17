<?php

/*
 * @copyright   2017 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactLedgerBundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\ReportBundle\Event\ReportBuilderEvent;
use Mautic\ReportBundle\Event\ReportGeneratorEvent;
use Mautic\ReportBundle\ReportEvents;

/**
 * Class ReportSubscriber.
 */
class ReportSubscriber extends CommonSubscriber
{
    const CONTEXT_CONTACT_LEDGER_CLIENT_STATS = 'contact_ledger_campaign_client_stats';

    const CONTEXT_CONTACT_LEDGER_SOURCE_STATS = 'contact_ledger_campaign_source_stats';

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ReportEvents::REPORT_ON_BUILD    => ['onReportBuilder', 0],
            ReportEvents::REPORT_ON_GENERATE => ['onReportGenerate', 0],
        ];
    }

    /**
     * Add available tables and columns to the report builder lookup.
     *
     * @param ReportBuilderEvent $event
     */
    public function onReportBuilder(ReportBuilderEvent $event)
    {
        if (!$event->checkContext([self::CONTEXT_CONTACT_LEDGER_CLIENT_STATS, self::CONTEXT_CONTACT_LEDGER_SOURCE_STATS])) {
            return;
        }

        if ($event->checkContext(self::CONTEXT_CONTACT_LEDGER_CLIENT_STATS)) {
            $this->onClientReportBuilder($event);
        }

        if ($event->checkContext(self::CONTEXT_CONTACT_LEDGER_SOURCE_STATS)) {
            $this->onSourceReportBuilder($event);
        }
    }

    /**
     * Initialize the QueryBuilder object to generate reports from.
     *
     * @param ReportGeneratorEvent $event
     */
    public function onReportGenerate(ReportGeneratorEvent $event)
    {
        $qb       = $event->getQueryBuilder();
        $dateFrom = $event->getOptions()['dateFrom'];
        $dateTo   = $event->getOptions()['dateTo'];

        $dateOffset = [
            'DAILY'   => '-1 day',
            'WEEKLY'  => '-7 days',
            'MONTHLY' => '- 30 days',
        ];
        if (empty($event->getReport()->getScheduleUnit())) {
            $dateShift = '- 30 days';
        } else {
            $dateShift = $dateOffset[$event->getReport()->getScheduleUnit()];
        }

        if (empty($dateFrom)) {
            $dateFrom = new \DateTime();
            $dateFrom->modify($dateShift);
        }

        if (empty($dateTo)) {
            $dateTo = new \DateTime();
        }

        $qb->andWhere('cls.date_added BETWEEN :dateFrom AND :dateTo')
            ->setParameter('dateFrom', $dateFrom->format('Y-m-d H:i:s'))
            ->setParameter('dateTo', $dateTo->format('Y-m-d H:i:s'));

        if ($event->checkContext(self::CONTEXT_CONTACT_LEDGER_CLIENT_STATS)) {
            $qb->select('SUM(cls.revenue / cls.received) as rpu, SUM(cls.revenue / 1000) AS rpm');
            $qb->leftJoin('cls', MAUTIC_TABLE_PREFIX.'contactclient', 'cc', 'cc.id = cls.contact_client_id');
            $catPrefix = 'cc';
            $from      = 'contact_ledger_campaign_client_stats';
        } elseif ($event->checkContext(self::CONTEXT_CONTACT_LEDGER_SOURCE_STATS)) {
            $qb->leftJoin('cls', MAUTIC_TABLE_PREFIX.'contactsource', 'cs', 'cs.id = cls.contact_source_id');
            $catPrefix = 'cs';
            $from      = 'contact_ledger_campaign_source_stats';
        } else {
            return;
        }

        $qb->from(MAUTIC_TABLE_PREFIX.$from, 'cls')
            ->leftJoin('cls', MAUTIC_TABLE_PREFIX.'campaigns', 'c', 'c.id = cls.campaign_id');

        $event->addCategoryLeftJoin($qb, $catPrefix, 'cat');

        $event->setQueryBuilder($qb);
    }

    /**
     * @param ReportBuilderEvent $event
     */
    private function onClientReportBuilder(ReportBuilderEvent $event)
    {
        $prefix              = 'cls.';
        $aliasPrefix         = 'cls_';
        $campaignPrefix      = 'c.';
        $campaignAliasPrefix = 'c_';
        $clientPrefix        = 'cc.';
        $clientAliasPrefix   = 'cc_';
        $catPrefix           = 'cat.';
        $catAliasPrefix      = 'cat_';

        $columns = [
            $prefix.'date_added' => [
                'label' => 'mautic.contactledger.dashboard.client-revenue.header.dateadded',
                'type'  => 'datetime',
                'alias' => $aliasPrefix.'date_added',
            ],

            $prefix.'campaign_id' => [
                'label' => 'mautic.contactledger.dashboard.client-revenue.header.id',
                'type'  => 'int',
                'alias' => $campaignAliasPrefix.'campaign_id',
            ],

            $campaignPrefix.'name' => [
                'label' => 'mautic.contactledger.dashboard.client-revenue.header.name',
                'type'  => 'string',
                'alias' => $campaignAliasPrefix.'name',
            ],

            $prefix.'contact_client_id' => [
                'label'          => 'mautic.contactledger.dashboard.client-revenue.header.clientid',
                'type'           => 'int',
                'alias'          => $aliasPrefix.'contact_client_id',
                'groupByFormula' => $prefix.'contact_client_id',
            ],

            $clientPrefix.'name' => [
                'label' => 'mautic.contactledger.dashboard.client-revenue.header.clientname',
                'type'  => 'string',
                'alias' => $clientAliasPrefix.'contact_client_id',
            ],

            $catPrefix.'title' => [
                'label'          => 'mautic.contactledger.dashboard.client-revenue.header.category',
                'type'           => 'string',
                'alias'          => $catAliasPrefix.'title',
                'groupByFormula' => $catPrefix.'title',
            ],

            $prefix.'revenue'    => [
                'label'   => 'mautic.contactledger.dashboard.client-revenue.header.revenue',
                'type'    => 'float',
                'alias'   => $aliasPrefix.'revenue',
                'formula' => 'SUM('.$prefix.'revenue)',
            ],
            $prefix.'ecpm'       => [
                'label'   => 'mautic.contactledger.dashboard.client-revenue.header.ecpm',
                'type'    => 'float',
                'alias'   => $aliasPrefix.'ecpm',
                'formula' => 'SUM('.$prefix.'ecpm)',
            ],
            $prefix.'received'   => [
                'label'   => 'mautic.contactledger.dashboard.client-revenue.header.received',
                'type'    => 'int',
                'alias'   => $aliasPrefix.'received',
                'formula' => 'SUM('.$prefix.'received)',
            ],
            $prefix.'declined'   => [
                'label'   => 'mautic.contactledger.dashboard.client-revenue.header.declined',
                'type'    => 'int',
                'alias'   => $aliasPrefix.'declined',
                'formula' => 'SUM('.$prefix.'declined)',
            ],
            $prefix.'converted'  => [
                'label'   => 'mautic.contactledger.dashboard.client-revenue.header.converted',
                'type'    => 'int',
                'alias'   => $aliasPrefix.'converted',
                'formula' => 'SUM('.$prefix.'converted)',
            ],
            $prefix.'utm_source' => [
                'label'          => 'mautic.contactledger.dashboard.client-revenue.header.utmsource',
                'type'           => 'string',
                'alias'          => $aliasPrefix.'utm_source',
                'groupByFormula' => $prefix.'utm_source',
            ],
            $prefix.'rpu'        => [
                'label'   => 'mautic.contactledger.dashboard.client-revenue.header.rpu',
                'type'    => 'float',
                'alias'   => 'rpu',
                'formula' => 'SUM('.$prefix.'revenue) / SUM('.$prefix.'received)',
                'formula' => 'SUM('.$prefix.'revenue) / SUM('.$prefix.'received)',
            ],
        ];

        $data = [
            'display_name' => 'mautic.widget.campaign.client.revenue',
            'columns'      => $columns,
        ];
        $event->addTable(self::CONTEXT_CONTACT_LEDGER_CLIENT_STATS, $data, 'contact_ledger_campaign_revenue');
    }

    private function onSourceReportBuilder(ReportBuilderEvent $event)
    {
        $prefix              = 'cls.';
        $aliasPrefix         = 'cls_';
        $campaignPrefix      = 'c.';
        $campaignAliasPrefix = 'c_';
        $sourcePrefix        = 'cs.';
        $sourceAliasPrefix   = 'cs_';
        $catPrefix           = 'cat.';
        $catAliasPrefix      = 'cat_';

        $columns = [
            $prefix.'date_added' => [
                'label' => 'mautic.contactledger.dashboard.source-revenue.header.dateadded',
                'type'  => 'datetime',
                'alias' => $aliasPrefix.'date_added',
            ],

            $prefix.'campaign_id' => [
                'label' => 'mautic.contactledger.dashboard.source-revenue.header.id',
                'type'  => 'int',
                'alias' => $aliasPrefix.'campaign_id',
            ],

            $campaignPrefix.'name' => [
                'label' => 'mautic.contactledger.dashboard.source-revenue.header.name',
                'type'  => 'string',
                'alias' => $campaignAliasPrefix.'name',
            ],

            $prefix.'contact_source_id' => [
                'label'          => 'mautic.contactledger.dashboard.source-revenue.header.sourceid',
                'type'           => 'int',
                'alias'          => $aliasPrefix.'contact_source_id',
                'groupByFormula' => $prefix.'contact_source_id',
            ],

            $sourcePrefix.'name' => [
                'label' => 'mautic.contactledger.dashboard.source-revenue.header.sourcename',
                'type'  => 'string',
                'alias' => $sourceAliasPrefix.'contact_source_id',
            ],

            $catPrefix.'title' => [
                'label'          => 'mautic.contactledger.dashboard.source-revenue.header.category',
                'type'           => 'string',
                'alias'          => $catAliasPrefix.'title',
                'groupByFormula' => $catPrefix.'title',
            ],

            $prefix.'revenue' => [
                'label'   => 'mautic.contactledger.dashboard.source-revenue.header.revenue',
                'type'    => 'float',
                'alias'   => $aliasPrefix.'revenue',
                'formula' => 'SUM('.$prefix.'revenue)',
            ],

            $prefix.'cost' => [
                'label'   => 'mautic.contactledger.dashboard.source-revenue.header.cost',
                'type'    => 'float',
                'alias'   => $aliasPrefix.'cost',
                'formula' => 'SUM('.$prefix.'cost)',
            ],

            $prefix.'ecpm' => [
                'label'   => 'mautic.contactledger.dashboard.source-revenue.header.ecpm',
                'type'    => 'float',
                'alias'   => $aliasPrefix.'ecpm',
                'formula' => 'IF(SUM('.$prefix.'revenue) > 0, ((SUM('.$prefix.'revenue) - SUM('.$prefix.'cost)) / 1000), 0)',
            ],

            $prefix.'gm' => [
                'label'   => 'mautic.contactledger.dashboard.source-revenue.header.gm',
                'type'    => 'float',
                'alias'   => $aliasPrefix.'gm',
                'formula' => 'IF(SUM('.$prefix.'revenue) > 0, (SUM('.$prefix.'revenue) - SUM('.$prefix.'cost)), 0)',
            ],

            $prefix.'margin'   => [
                'label'   => 'mautic.contactledger.dashboard.source-revenue.header.margin',
                'type'    => 'float',
                'alias'   => $aliasPrefix.'margin',
                'formula' => 'IF(SUM('.$prefix.'revenue) > 0, ((SUM('.$prefix.'revenue) - SUM('.$prefix.'cost)) / SUM('.$prefix.'revenue)), 0)',
            ],

            $prefix.'received' => [
                'label'   => 'mautic.contactledger.dashboard.source-revenue.header.received',
                'type'    => 'int',
                'alias'   => $aliasPrefix.'received',
                'formula' => 'SUM('.$prefix.'received)',
            ],

            $prefix.'scrubbed' => [
                'label'   => 'mautic.contactledger.dashboard.source-revenue.header.scrubbed',
                'type'    => 'int',
                'alias'   => $aliasPrefix.'scrubbed',
                'formula' => 'SUM('.$prefix.'scrubbed)',
            ],

            $prefix.'declined'   => [
                'label'   => 'mautic.contactledger.dashboard.source-revenue.header.declined',
                'type'    => 'int',
                'alias'   => $aliasPrefix.'declined',
                'formula' => 'SUM('.$prefix.'declined)',
            ],
            $prefix.'converted'  => [
                'label'   => 'mautic.contactledger.dashboard.source-revenue.header.converted',
                'type'    => 'int',
                'alias'   => $aliasPrefix.'converted',
                'formula' => 'SUM('.$prefix.'converted)',
            ],
            $prefix.'utm_source' => [
                'label'          => 'mautic.contactledger.dashboard.source-revenue.header.utmsource',
                'type'           => 'string',
                'alias'          => $aliasPrefix.'utm_source',
                'groupByFormula' => $prefix.'utm_source',
            ],
        ];

        $data = [
            'display_name' => 'mautic.widget.campaign.source.revenue',
            'columns'      => $columns,
        ];
        $event->addTable(self::CONTEXT_CONTACT_LEDGER_SOURCE_STATS, $data, 'contact_ledger_campaign_revenue');
    }
}
