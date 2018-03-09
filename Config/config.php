<?php
return [
    'name' => 'Mautic Contact Ledger',
    'description' => 'Adds cost and revenue tracking on a per ler lead basis.',
    'version'     => '1.0.0',
    'author'      => 'Nicholai Bush',

    'services' => [
        'events' => [
            'mautic.contactledger.eventlistener.lead' => [
                'class' => \MauticPlugin\MauticContactLedgerBundle\EventListener\LeadSubscriber::class,
                'arguments' => [
                    '@mautic.contactledger.model.entry'
                ],
            ],
        ],
        'models' => [
            'mautic.contactledger.model.entry' => [
                'class' => \MauticPlugin\MauticContactLedgerBundle\Model\EntryModel::class,
            ],
        ],
    ],
];
