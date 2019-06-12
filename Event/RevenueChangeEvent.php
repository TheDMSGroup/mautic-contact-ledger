<?php

namespace MauticPlugin\MauticContactLedgerBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class RevenueChangeEvent.
 *
 * @package MauticPlugin\MauticContactLedgerBundle\Event
 */
class RevenueChangeEvent extends Event
{
    /**
     * @var array
     */
    private $webhook_payload = [];

    /**
     * RevenueChangeEvent constructor.
     *
     * @param array $webhook_payload
     */
    public function __construct(array $webhook_payload)
    {
        $this->webhook_payload = $webhook_payload;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->webhook_payload;
    }
}
