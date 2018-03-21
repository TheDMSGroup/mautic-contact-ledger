<?php
/**
 * Created by PhpStorm.
 * User: nbush
 * Date: 3/20/18
 * Time: 2:29 PM
 */

namespace MauticPlugin\MauticContactLedgerBundle\EventListener;

use MauticPlugin\MauticContactLedgerBundle\Event\ContactLedgerContextEvent;
use MauticPlugin\MauticContactLedgerBundle\Event\ContactLedgerContextEventInterface;

class ContactLedgerContextSubscriber
{
    /**
     * @var
     */
    protected $campaignId;

    protected $actor;

    protected $type;

    protected $amount;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'mautic.contact_ledger.create_context' => ['setContext', 0],
        ];
    }

    /**
     * @param ContactLedgerContextEvent $event
     */
    public function setContext(ContactLedgerContextEventInterface $event)
    {
        $this->campaign = $event->getCampaign();
        $this->actor    = $event->getActor();
        $this->type     = $event->getType();
        $this->amount   = $event->getAmount();
    }

    /**
     * @return CampaignId|null
     */
    public function getCampaignId()
    {
        if (isset($this->campaign)) {
            return $this->campaign->getId();
        }

        return null;
    }

    /**
     * @return Campaign|null
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