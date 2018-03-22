<?php

return [
    'name'        => 'Mautic Contact Ledger',
    'description' => 'Adds cost and revenue tracking on a per ler lead basis.',
    'version'     => '1.0.0',
    'author'      => 'Nicholai Bush',

    'services' => [
        'events' => [
            'mauticplugin.contactledger.subscriber.lead'            => [
                'class'     => \MauticPlugin\MauticContactLedgerBundle\EventListener\LeadSubscriber::class,
                'arguments' => [
                    '@mauticplugin.contactledger.model.entry',
                    '@mauticplugin.contactledger.subscriber.context_create',
                    '@logger',
                ],
            ],
            'mauticplugin.contactledger.subscriber.context_create'  => [
                'class' => \MauticPlugin\MauticContactLedgerBundle\EventListener\ContactLedgerContextSubscriber::class,
            ],
            'mauticplugin.contactledger.subscriber.context_capture' => [
                'class'     => \MauticPlugin\MauticContactLedgerBundle\EventListener\ContactLedgerContextCaptureSubscriber::class,
                'arguments' => [
                    '@mauticplugin.contactledger.model.entry',
                    '@mauticplugin.contactledger.subscriber.context_create',
                    '@logger',
                ],
            ],
            'mautic.contactledger.dashboard.subscriber' => [
                'class'     => \MauticPlugin\MauticContactLedgerBundle\EventListener\DashboardSubscriber::class,
                'arguments' => [
                    'mautic.contactledger.model.entry',
                ],
            ],
        ],
        'models' => [
            'mauticplugin.contactledger.model.entry' => [
                'class' => \MauticPlugin\MauticContactLedgerBundle\Model\EntryModel::class,
            ],
        ],
    ],
];
