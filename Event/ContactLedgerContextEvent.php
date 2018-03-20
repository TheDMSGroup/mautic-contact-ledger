<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic Community
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactLedgerBundle\Event;

use Mautic\CampaignBundle\Entity\Campaign;
use Symfony\Component\EventDispatcher\Event;

class ContactLedgerContextEvent extends Event
{
    const COST    = 'cost';

    const MEMO    = 'memo';

    const REVENUE = 'revenue';

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
    protected $eventType;

    /**
     * ContactLedgerContextEvent constructor.
     *
     * @param Campaign $campaign
     * @param          $actor
     * @param          $eventType
     */
    public function __construct(Campaign $campaign, $actor, $eventType)
    {
        $this->campaign  = $campaign;
        $this->actor     = $actor;
        $this->eventType = $eventType;
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
