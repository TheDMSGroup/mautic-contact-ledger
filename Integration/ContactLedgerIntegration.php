<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Digital Media Solutions, LLC
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

    /**
     * @param \Mautic\PluginBundle\Integration\Form|\Symfony\Component\Form\FormBuilder $builder
     * @param array                                                                     $data
     * @param string                                                                    $formArea
     */
    public function appendToForm(&$builder, $data, $formArea)
    {
        if ('features' == $formArea) {
            $builder->add('campaign_list', 'text');
        }
    }
}
