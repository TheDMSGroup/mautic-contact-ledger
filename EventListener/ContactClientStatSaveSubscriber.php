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

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContactClientStatSaveSubscriber implements EventSubscriberInterface
{
    /**
     * @var array
     */
    private $updatedDates = [];

    /** @var EntityManager */
    private $em;

    /** CampaignClientStatsRepository $campaignClientStatRepository */
    private $campaignClientStatRepository;

    /**
     * ContactClientStatSaveSubscriber constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->campaignClientStatRepository = $em->getRepository('MauticContactLedgerBundle:CampaignClientStats');
        $this->em                           = $em;
    }

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
     * @param $event
     */
    public function updateCampaignClientStatsRecords($event)
    {
        $contact   = $event->getContact();
        $dateAdded = $contact->getDateAdded();
        $dateAdded->setTime($dateAdded->format('H'), floor($dateAdded->format('i') / 5) * 5, 0);
        $ts     = $dateAdded->getTimestamp();
        $params = [
            'dateTo' => $dateAdded,
        ];

        if (!isset($this->updatedDates[$ts])) {
            $this->updatedDates[$ts] = true;
            $this->campaignClientStatRepository->updateExistingEntitiesByDate(
                $params,
                $this->em
            ); // expects $params['dateTo'] as rounded to 5 mins
        }
    }
}
