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

use Mautic\CampaignBundle\Entity\Campaign;
use MauticPlugin\MauticContactLedgerBundle\Event\ContactLedgerContextEvent;

class ContactLedgerContextSubscriber
{
    /** @var Campaign */
    protected $campaign;

    protected $actor;

    protected $eventType;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'mauticplugin.contact_ledger.create_context' => ['setContext', 0],
        ];
    }

    /**
     * @param ContactLedgerContextEvent $event
     */
    public function setContext(ContactLedgerContextEvent $event)
    {
        $this->campaign  = $event->getCampaign();
        $this->actor     = $event->getActor();
        $this->eventType = $event->getEventType();
    }

    /**
     * @return Campaign
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * @return object
     */
    public function getActor()
    {
        return $this->actor;
    }

    /**
     * @return string
     */
    public function getEventType()
    {
        return $this->eventType;
    }
}
