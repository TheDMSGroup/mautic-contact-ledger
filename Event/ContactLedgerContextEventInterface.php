<?php
/**
 * Created by PhpStorm.
 * User: nbush
 * Date: 3/21/18
 * Time: 12:19 AM.
 */

namespace MauticPlugin\MauticContactLedgerBundle\Event;

interface ContactLedgerContextEventInterface
{
    /**
     * @return mixed
     */
    public function getCampaign();

    /**
     * @return object|null
     */
    public function getActor();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return float|string|null
     */
    public function getAmount();
}
