<?php

namespace MauticPlugin\MauticContactLedgerBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Mautic\CampaignBundle\Entity\Campaign;

class ContactLedgerContextEvent extends Event implements ContactLedgerContextEventInterface
{
    /**
     * This type will use the cost collumn to record entryAmount
     */
    const ENTRY_TYPE_COST    = 'cost';

    /**
     * This type wil use the revenue colloumn to record entryAmount
     */
    const ENTRY_TYPE_REVENUE = 'revenue';

    /**
     * This type will use the memo collumn to record entryAmount.
     * Non-decimal strings for entryAmount are valid for this type.
     */
    const ENTRY_TYPE_MEMO    = 'memo';

    /**
     * @var Campaign|null
     */
    protected $campaign;

    /**
     * @var object|null
     */
    protected $actor;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string|float/null
     */
    private $amount;

    /**
     * ContactLedgerContextEvent constructor.
     *
     * @param Campaign $campaign
     * @param object $actor
     * @param string $type
     * @param string|float|null $amount
     */
    public function __construct(Campaign $campaign=null, $actor=null, $type='memo', $amount=null)
    {
        $this->campaign = $campaign;
        $this->actor    = $actor;
        $this->type     = $type;
        $this->amount   = $amount;
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
     * @return float|string|null
     */
    public function getAmount()
    {
        return $this->amount;
    }
}