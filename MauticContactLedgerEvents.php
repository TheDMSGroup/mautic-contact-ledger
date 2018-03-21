<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic Community
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactLedgerBundle;

/**
 * Class MauticContactLedgerEvents.
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
