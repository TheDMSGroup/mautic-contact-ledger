<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic Community
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactLedgerBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;
use Mautic\LeadBundle\Entity\Lead;

/**
 * Class EntryRepository extends {@see \Mautic\CoreBundle\Entity\CommonRepository}.
 */
class LedgerEntryRepository extends CommonRepository
{
    /**
     * Defines default table alias for contact_ledger table.
     *
     * @return string
     */
    public function getTableAlias()
    {
        return 'cl';
    }

    /**
     * @param \Mautic\LeadBundle\Entity\Lead $contact
     *
     * @return LedgerEntry[]
     */
    public function getContactLedger(Lead $contact)
    {
        return [];
    }

    /**
     * @param \Mautic\LeadBundle\Entity\Lead $contact
     *
     * @return string|float
     */
    public function getContactCost(Lead $contact)
    {
        return '';
    }

    /**
     * @param \Mautic\LeadBundle\Entity\Lead $contact
     *
     * @return string|float
     */
    public function getContactRevenue(Lead $contact)
    {
        return '';
    }
}
