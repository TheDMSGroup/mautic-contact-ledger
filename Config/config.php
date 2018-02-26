<?php
return [
    'name' => 'Mautic Contact Ledger',
    'description' => 'Adds cost and revenue tracking on a per ler lead basis.',
    'version'     => '1.0.0',
    'author'      => 'Nicholai Bush',
    
    'services' => [
        'other' => [
            'mautic.contactledger.helper.entity_listener' => [
                'class' => \MauticPlugin\MauticContactLedgerBundle\Helper\ContactListenr::class,
                'arguments' => [
                    'logger'
                ]
            ],
        ],
    ],
    'models' => [
        'mautic.contactledger.model.entry' => [
            'class' => \MauticPlugin\MauticContactLedgerBundle\Model\EntryModel::class,
            'arguments' => []
        ]
    ]
];


// 