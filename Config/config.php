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
                    '@mauticplugin.contactledger.model.ledgerentry',
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
                    '@mauticplugin.contactledger.model.ledgerentry',
                    '@mauticplugin.contactledger.subscriber.context_create',
                    '@logger',
                ],
            ],
            'mauticplugin.contactledger.subscriber.customcontent'  => [
                'class' => \MauticPlugin\MauticContactLedgerBundle\EventListener\CustomContentSubscriber::class,
                'arguments' => [
                    '@mauticplugin.contactledger.model.ledgerentry',
                ],
            ],
        ],
        'models' => [
            'mauticplugin.contactledger.model.ledgerentry' => [
                'class' => \MauticPlugin\MauticContactLedgerBundle\Model\LedgerEntryModel::class,
            ],
        ],
    ],
];
