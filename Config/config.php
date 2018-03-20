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
            'mautic.contactledger.subcriber.lead'     => [
                'class'     => \MauticPlugin\MauticContactLedgerBundle\EventListener\LeadSubscriber::class,
                'arguments' => [
                    '@mautic.contactledger.model.entry',
                    '@logger',
                ],
            ],
            'mautic.contactledger.subcriber.enhancer' => [
                'class'     => \MauticPlugin\MauticContactLedgerBundle\EventListener\EnhancerSubscriber::class,
                'arguments' => [
                    '@mautic.contactledger.model.entry',
                    '@logger',
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
