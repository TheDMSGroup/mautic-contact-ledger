<?php
/**
 * Created by PhpStorm.
 * User: nbush
 * Date: 3/20/18
 * Time: 2:29 PM
 */

namespace MauticPlugin\MauticContactLedgerBundle\EventListener;

use MauticPlugin\MauticContactLedgerBundle\Event\ContactLedgerContextEvent;

class ContactLedgerContextSubscriber
{

    protected $campaign;

    protected $actor;

    protected $entryType;

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
        $this->campaign = $event->getCampaign();
        $this->actor    = $event->getActor();
        $this->entryType = $event->getEventType();
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