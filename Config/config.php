<?php

return [
    'name'        => 'Mautic Contact Ledger',
    'description' => 'Adds cost and revenue tracking on a per ler lead basis.',
    'version'     => '1.0.0',
    'author'      => 'Nicholai Bush',

    'services' => [
        'events' => [
            'mauticplugin.contactledger.subcriber.lead'     => [
                'class'     => \MauticPlugin\MauticContactLedgerBundle\EventListener\LeadSubscriber::class,
                'arguments' => [
                    '@mauticplugin.contactledger.model.entry',
                    '@mauticplugin.contactledger.subcriber.ledger_context',
                    '@logger',
                ],
            ],
            'mauticplugin.contactledger.subcriber.ledger_context' => [
                'class'     => \MauticPlugin\MauticContactLedgerBundle\EventListener\ContactLedgerContextSubscriber::class,
            ],
            'mauticplugin.contactledger.subcriber.enhancer' => [
                'class'     => \MauticPlugin\MauticContactLedgerBundle\EventListener\EnhancerSubscriber::class,
                'arguments' => [
                    '@mautic.contactledger.model.entry',
                    '@logger',
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
