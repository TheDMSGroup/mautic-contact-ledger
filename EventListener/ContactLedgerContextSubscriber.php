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
use MauticPlugin\MauticContactLedgerBundle\Event\ContactLedgerContextEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContactLedgerContextSubscriber implements EventSubscriberInterface
{
    /** @var Campaign */
    protected $campaign;

    protected $actor;

    protected $type;

    protected $amount;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'mauticplugin.contactledger.create_context' => ['setContext', 0],
        ];
    }

    /**
     * @param ContactLedgerContextEventInterface $event
     */
    public function setContext(ContactLedgerContextEventInterface $event)
    {
        $this->campaign = $event->getCampaign();
        $this->actor    = $event->getActor();
        $this->type     = $event->getType();
        $this->amount   = $event->getAmount();
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
