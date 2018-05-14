<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic Community
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactLedgerBundle\EventListener;

use MauticPlugin\MauticContactLedgerBundle\Event\ReportStatsGeneratorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CampaignSourceStatsSubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'mautic.contactledger.reportstats.generate' => ['generateReportStats', 0],
        ];
    }


    public function generateReportStats(ReportStatsGeneratorEvent $event)
    {
        if ($event->getContext() == 'CampaignSourceStats') {

            $params   = $event->getParams();
            $cacheDir = $params['cacheDir'];
            $em       = $event->getEntityManager();

            //first check to see if the table has any records in the params range
            // do this before running the more intensive query below
            $repo   = $em->getRepository(\MauticPlugin\MauticContactLedgerBundle\Entity\LedgerEntry::class);
            $entity = $repo->getEntityGreaterThanDate($params);
            if (!empty($entity)) {
                $nextDate = $entity->getDateAdded();
                $dateTo   = new \DateTime($params['dateTo']);
                $dateFrom = new \DateTime($params['dateFrom']);
                if ($nextDate > $dateTo) {
                    // No records within original query range
                    // reset the date range to one that has data.
                    $dateFrom           = clone $nextDate;
                    $params['dateFrom'] = $dateFrom->format('Y-m-d H:i:s');
                    $dateTo             = clone $nextDate;
                    $dateTo->add(new \DateInterval('PT5M'));
                    $params['dateTo'] = $dateTo->format('Y-m-d H:i:s');
                }

                //dont let date range span multiple calendar days, reformat 'To' to less than 5 mins instead, ending at 1 sec before midnight.
                $dateFrom = new \DateTime($params['dateFrom']);
                $dateTo   = new \DateTime($params['dateTo']);

                if ($dateFrom->diff($dateTo)->d !== 0) {
                    $dateTo = clone $dateFrom;
                    $dateTo->format('Y-m-d 23:59:59');
                    $params['dateTo'] = $dateTo;
                }

                // update the $event with latest params
                $event->setParams($params);
                // do a final check to make sure we are NOT within 15 mins of now (because of adjusted date)
                $now = new \DateTime();
                $now->sub(new \DateInterval('PT15M'));
                if ($now > $nextDate) {
                    //now do final query for results - this may take a while
                    $data = $repo->getDashboardRevenueWidgetData(
                        $params,
                        true,
                        $cacheDir,
                        false
                    );// expects $params['dateFrom'] & $params['dateTo']
                }
            }

        }

        if ($event->getContext() == 'CampaignSourceBudgets') {
            return;
        }

        if (!empty($data)) {
            $statsCollection                = $event->getStatsCollection();
            $statsCollection[static::class] = [$event->getContext() => $data];
            $event->setStatsCollection($statsCollection);
        }

    }
}
