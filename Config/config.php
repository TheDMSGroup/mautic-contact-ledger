<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic Community
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

return [
    'name'        => 'Mautic Contact Ledger',
    'description' => 'Adds cost and revenue tracking on a per ler lead basis.',
    'version'     => '1.0.0',
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
                    '@mautic.dashboard.model.dashboard'
                ],
            ],
            'mautic.contactledger.dashboard.subscriber'       => [
                'class'     => 'MauticPlugin\MauticContactLedgerBundle\EventListener\DashboardSubscriber',
                'arguments' => [
                    'mautic.contactledger.model.ledgerentry',
                ],
            ],
        ],
        'models' => [
            'mautic.contactledger.model.ledgerentry' => [
                'class' => 'MauticPlugin\MauticContactLedgerBundle\Model\LedgerEntryModel',
            ],
        ],
    ],
];
