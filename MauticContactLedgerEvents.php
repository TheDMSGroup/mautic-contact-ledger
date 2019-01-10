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
     * Listen for a sourcestats.generate event and add stats if the context matches.
     */
    const SOURCE_STATS_GENERATE = 'mautic.contactledger.sourcestats.generate';

    /**
     * Listen for a clientstats.generate event and add stats if the context matches.
     */
    const CLIENT_STATS_GENERATE = 'mautic.contactledger.clientstats.generate';

    /**
     * Listen for a chartdata.alter event and allow other bundles to alter the ledger chart data.
     */
    const CHART_DATA_ALTER = 'mautic.contactledger.chartdata.alter';
}
