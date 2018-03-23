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
