<?php

namespace MauticPlugin\MauticContactLedgerBundle;
/**
 * Created by PhpStorm.
 * User: nbush
 * Date: 3/20/18
 * Time: 4:01 PM
 */
final class MauticContactLedgerEvents
{
    /**
     * To use this plugin properly, dispatch a
     *
     * MauticPlugin\MaucticContactLedgerBundle\Event\ContactLedgerContextEvent
     * or a compatible event
     */
    const CREATE_CONTEXT = 'mauticplugin.contact_ledger.create_context';
}