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

use MauticPlugin\MauticContactClientBundle\Event\ContactClientStatEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContactClientStatSaveSubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'mautic.contactclient_stat_save' => ['updateCampaignClientStatsRecords', 0],
        ];
    }

    /**
     * @param ContactClientStatEvent $event
     */
    public function updateCampaignClientStatsRecords(ContactClientStatEvent $event)
    {
        $contact          = $event->getContact();
        $em               = $event->getEntityManager();

        //first check to see if the table has any records in the params range
        // do this before running the more intensive query below

        $repo      = $em->getRepository(\MauticPlugin\MauticContactLedgerBundle\Entity\CampaignClientStats::class);
        $dateAdded = $contact->getDateAdded();
        $dateAdded->setTime($dateAdded->format('H'), floor($dateAdded->format('i') / 5) * 5, 0);
        $params = [
            'dateTo' => $dateAdded,
        ];

        $existingEntities = $repo->getExistingEntitiesByDate($params); // expects $params['dateTo'] as rounded to 5 mins

        if ($existingEntities) {
            foreach ($existingEntities as $entity) {
                $entity->setReprocessFlag(true);
                $em->persist($entity);
            }
            $em->flush();
        }
    }
}
