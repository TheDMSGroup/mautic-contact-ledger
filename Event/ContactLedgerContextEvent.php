<?php

namespace MauticPlugin\MauticContactLedgerBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Mautic\CampaignBundle\Entity\Campaign;

class ContactLedgerContextEvent extends Event
{
    const COST    = 'cost';

    const REVENUE = 'revenue';

    const MEMO    = 'memo';

    /**
     * @var Campaign
     */
    protected $campaign;

    /**
     * @var object
     */
    protected $actor;

    /**
     * @var string
     */
    protected $entryType;

    /**
     * ContactLedgerContextEvent constructor.
     *
     * @param Campaign $campaign
     * @param object $actor
     * @param string $entryType
     */
    public function __construct(Campaign $campaign, $actor, $entryType)
    {
        $this->campaign  = $campaign;
        $this->actor     = $actor;
        $this->entryType = $entryType;
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