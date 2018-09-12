<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Digital Media Solutions, LLC
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactLedgerBundle\EventListener;

use MauticPlugin\MauticContactLedgerBundle\Event\ReportStatsGeneratorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CampaignClientStatsSubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'mautic.contactledger.clientstats.generate' => ['generateReportStats', 0],
        ];
    }

    /**
     * @param ReportStatsGeneratorEvent $event
     *
     * @throws \Exception
     */
    public function generateReportStats(ReportStatsGeneratorEvent $event)
    {
        $data = null;

        if ('CampaignClientStats' == $event->getContext()) {
            $params   = $event->getParams();
            $cacheDir = $params['cacheDir'];
            $em       = $event->getEntityManager();

            //first check to see if the table has any records in the params range
            // do this before running the more intensive query below

            $repo = $em->getRepository(\MauticPlugin\MauticContactLedgerBundle\Entity\LedgerEntry::class);

            $data = $repo->getCampaignClientStatsData(
                $params,
                $cacheDir,
                false
            ); // expects $params['dateFrom'] & $params['dateTo']

        }

        $statsCollection                = $event->getStatsCollection();
        $statsCollection[static::class] = [$event->getContext() => $data];
        $event->setStatsCollection($statsCollection);
    }
}
