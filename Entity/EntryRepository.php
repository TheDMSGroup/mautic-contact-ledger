<?php

namespace MauticPlugin\MauticContactLedgerBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;
use Mautic\LeadBundle\Entity\Lead;

class EntryEntity extends CommonRepository
{
    /**
     * @param Lead
     *
     * @return Entry[]
     */
    public function getContactLedger(Lead $contact)
    {

    }

    public function getContactCost(Lead $contact)
    {

    }

    public function getContactRevenue(Lead $contact)
    {

    }

    /**
     * getObjectLedger/Cost/Revenue
     * getDateRangeLedger/CostRevenue
     */
}
