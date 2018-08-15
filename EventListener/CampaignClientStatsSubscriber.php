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

    public function generateReportStats(ClientStatsGeneratorEvent $event)
    {
        $data = null;

        if ('CampaignSourceStats' == $event->getContext()) {
            $params   = $event->getParams();
            $cacheDir = $params['cacheDir'];
            $em       = $event->getEntityManager();

            //first check to see if the table has any records in the params range
            // do this before running the more intensive query below

            $repo = $em->getRepository(\MauticPlugin\MauticContactLedgerBundle\Entity\LedgerEntry::class);

            do {
                $entity = $repo->getEntityGreaterThanDate($params);
                if (!empty($entity)) {
                    $nextDate = $entity->getDateAdded();
                    $dateTo   = new \DateTime($params['dateTo']);
                    if ($nextDate > $dateTo) {
                        // No records within original query range
                        // reset the date range to one that has data.
                        $nextDate->setTime($nextDate->format('H'), floor($nextDate->format('i') / 5) * 5, 0);
                        $nextDate->add(new \DateInterval('PT1S'));
                        $dateFrom           = clone $nextDate;
                        $params['dateFrom'] = $dateFrom->format('Y-m-d H:i:s');
                        $dateTo             = clone $nextDate;
                        $dateTo->add(new \DateInterval('PT4M'));
                        $dateTo->add(new \DateInterval('PT59S'));
                        $params['dateTo'] = $dateTo->format('Y-m-d H:i:s');
                    }

                    // update the $event with latest params
                    $event->setParams($params);
                    // do a final check to make sure we are NOT within 15 mins of now (because of adjusted date)
                    $now = new \DateTime();
                    $now->sub(new \DateInterval('PT15M'));
                    if ($now > $nextDate) {
                        //now do final query for results - this may take a while
                        $data = $repo->getCampaignSourceStatsData(
                            $params,
                            true,
                            $cacheDir,
                            false
                        ); // expects $params['dateFrom'] & $params['dateTo']
                    }
                }
                if (empty($data) && !empty($entity)) {
                    // add 5 mins to params['from'] and try again
                    $newFrom = new \DateTime($params['dateFrom']);
                    $newFrom->add(new \DateInterval('PT5M'));
                    $newTo = new \DateTime($params['dateTo']);
                    $newTo->add(new \DateInterval('PT5M'));
                    $params['dateFrom'] = $newFrom->format('Y-m-d H:i:s');
                    $params['dateTo']   = $newTo->format('Y-m-d H:i:s');
                } else {
                    break;
                }
                echo '.';
            } while (true);
        }

        if ('CampaignSourceBudgets' == $event->getContext()) {
            return;
        }

        $statsCollection                = $event->getStatsCollection();
        $statsCollection[static::class] = [$event->getContext() => $data];
        $event->setStatsCollection($statsCollection);
    }
}
