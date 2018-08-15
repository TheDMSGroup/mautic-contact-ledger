<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Digital Media Solutions, LLC
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

return [
    'name'        => 'Contact Ledger',
    'description' => 'Adds cost and revenue tracking on a per ler lead basis.',
    'version'     => '1.0',
    'author'      => 'Nicholai Bush',

    'services' => [
        'events' => [
            'mautic.contactledger.subscriber.lead'            => [
                'class'     => 'MauticPlugin\MauticContactLedgerBundle\EventListener\LeadSubscriber',
                'arguments' => [
                    '@mautic.contactledger.model.ledgerentry',
                    '@mautic.contactledger.subscriber.context_create',
                ],
            ],
            'mautic.contactledger.subscriber.context_create'  => [
                'class' => 'MauticPlugin\MauticContactLedgerBundle\EventListener\ContactLedgerContextSubscriber',
            ],
            'mautic.contactledger.subscriber.context_capture' => [
                'class'     => 'MauticPlugin\MauticContactLedgerBundle\EventListener\ContactLedgerContextCaptureSubscriber',
                'arguments' => [
                    '@mautic.contactledger.model.ledgerentry',
                    '@mautic.contactledger.subscriber.context_create',
                ],
            ],
            'mautic.contactledger.subscriber.customcontent'   => [
                'class'     => 'MauticPlugin\MauticContactLedgerBundle\EventListener\CustomContentSubscriber',
                'arguments' => [
                    '@mautic.contactledger.model.ledgerentry',
                    '@mautic.dashboard.model.dashboard',
                ],
            ],
            'mautic.contactledger.dashboard.subscriber'       => [
                'class'     => 'MauticPlugin\MauticContactLedgerBundle\EventListener\DashboardSubscriber',
                'arguments' => [
                    'mautic.contactledger.model.ledgerentry',
                ],
            ],
            'mautic.contactledger.sourcestats.generate'       => [
                'class'     => 'MauticPlugin\MauticContactLedgerBundle\EventListener\CampaignSourceStatsSubscriber',
                'arguments' => [
                ],
            ],
            'mautic.contactledger.clientstats.generate'       => [
                'class'     => 'MauticPlugin\MauticContactLedgerBundle\EventListener\CampaignClientStatsSubscriber',
                'arguments' => [
                ],
            ],
        ],
        'models' => [
            'mautic.contactledger.model.ledgerentry' => [
                'class' => 'MauticPlugin\MauticContactLedgerBundle\Model\LedgerEntryModel',
            ],
        ],
        'forms' => [
            'mautic.contactledger.form.type.campaign_source_revenue_widget' => [
                'class'     => 'MauticPlugin\MauticContactLedgerBundle\Form\Type\CampaignSourceRevenueWidgetType',
                'alias'     => 'campaign_source_revenue_widget',
            ],
        ],
    ],
];
