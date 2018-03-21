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
use MauticPlugin\MauticContactLedgerBundle\MauticContactLedgerEvents;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContactLedgerContextSubscriber implements EventSubscriberInterface
{
    /** @var Campaign|null */
    protected $campaign;

    /** @var object|null */
    protected $actor;

    /** @var string */
    protected $type;

    /** @var string|float|null */
    protected $amount;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            MauticContactLedgerEvents::CREATE_CONTEXT => ['setContext', 0],

        ];
    }

    /**
     * This method expects an event compatable with the definition in
     * \MauticPlugin\MauticContactLedgerBundle\Event\ContactLedgerContextEventInterface
     * However, to minimize inter-plugin dependecies, any \Symfony\Component\EventDispatcher\Event
     * is allowed
     *
     */
    public function setContext(Event $event)
    {
        if (method_exists($event, 'getCampaign')) {
           $this->campaign = $event->getCampaign();
    }
        if (method_exists($event, 'getActor')) {
            $this->actor    = $event->getActor();
        }
        if (method_exists($event, 'getType')) {
            $this->type     = $event->getType();
        }
        if (method_exists($event, 'getAmount')) {
            $this->amount = $event->getAmount();
        }
    }

    /**
     * @return Campaign
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * @return object|null
     */
    public function getActor()
    {
        return $this->actor;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string|float|null
     */
    public function getAmount()
    {
        return $this->amount;
    }
}
