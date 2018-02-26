<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Nicholai Bush <nbush@thedmsgrp.com>
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactLedgerBundle\Integration;

use Mautic\PluginBundle\Integration\AbstractIntegration;

class ContactLedgerIntegration extends AbstractIntegration
{
    public function getName()
    {
        return 'ContactLedger';
    }
    
    public function getDisplayName()
    {
        return 'Contact Ledger';    
    }
    
    public function getAuthenticationType()
    {
        return 'none';
    }
}