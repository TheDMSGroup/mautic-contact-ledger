<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Digital Media Solutions, LLC
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
    const CONTEXT_CAPTURE = 'mautic.contactledger.context_capture';

    /**
     * To use this plugin properly, dispatch a MauticPlugin\MaucticContactLedgerBundle\Event\ContactLedgerContextEvent
     * or a compatible event.
     */
    const CONTEXT_CREATE = 'mautic.contactledger.context_create';

    /**
     * Listen for a reportstats.generate event and add stats if the context matches.
     */
    const REPORT_STATS_GENERATE = 'mautic.contactledger.reportstats.generate';
}
