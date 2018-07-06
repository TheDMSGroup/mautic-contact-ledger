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

use Mautic\CampaignBundle\Entity\Campaign;
use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\MauticContactLedgerBundle\MauticContactLedgerEvents;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContactLedgerContextSubscriber implements EventSubscriberInterface
{
    /** @var Campaign|null */
    protected $campaign;

    /** @var object|null */
    protected $actor;

    /** @var object|null */
    protected $activity;

    /** @var string */
    protected $memo;

    /** @var \Mautic\LeadBundle\Entity\Lead */
    protected $lead;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            MauticContactLedgerEvents::CONTEXT_CREATE => ['contextCreate', 0],
        ];
    }

    /**
     * This method expects an event comparable with the definition in
     * \MauticPlugin\MauticContactLedgerBundle\Event\ContactLedgerContextEventInterface
     * However, to minimize inter-plugin dependencies, any \Symfony\Component\EventDispatcher\Event
     * is allowed.
     *
     * @param Event $event
     */
    public function contextCreate(Event $event)
    {
        if (method_exists($event, 'getCampaign')) {
            $this->campaign = $event->getCampaign();
        }
        if (method_exists($event, 'getActor')) {
            $this->actor = $event->getActor();
        }
        if (method_exists($event, 'getActivity')) {
            $this->activity = $event->getActivity();
        }
        if (method_exists($event, 'getMemo')) {
            $this->memo = $event->getMemo();
        }
        if (method_exists($event, 'getLead')) {
            $this->lead = $event->getLead();
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
     * @return object|null
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * @return string
     */
    public function getMemo()
    {
        return $this->memo;
    }

    /**
     * @return Lead
     */
    public function getLead()
    {
        return $this->lead;
    }
}
