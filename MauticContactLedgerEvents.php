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
     * Capture the current context, for situations where there was no pre-save event.
     */
    const CONTEXT_CAPTURE = 'mauticplugin.contactledger.context_capture';

    /**
     * To use this plugin properly, dispatch a MauticPlugin\MaucticContactLedgerBundle\Event\ContactLedgerContextEvent
     * or a compatible event.
     */
    const CONTEXT_CREATE = 'mauticplugin.contactledger.context_create';
}
