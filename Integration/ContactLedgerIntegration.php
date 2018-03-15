<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic Community
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactLedgerBundle\Integration;

use Mautic\PluginBundle\Integration\AbstractIntegration;

/**
 * Class ContactLedgerIntegration.
 */
class ContactLedgerIntegration extends AbstractIntegration
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'ContactLedger';
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return 'Contact Ledger';
    }

    /**
     * @return string
     */
    public function getAuthenticationType()
    {
        return 'none';
    }
}
