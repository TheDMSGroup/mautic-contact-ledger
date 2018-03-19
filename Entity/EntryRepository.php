<?php

namespace MauticPlugin\MauticContactLedgerBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;
use Mautic\LeadBundle\Entity\Lead;

class EntryRepository extends CommonRepository
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

    /**
     * @param $params
     *
     * @return array
     */
    public function getDashboardRevenueWidgetData($params)
    {
        $q = $this->createQueryBuilder('e');

        $expr = $q->expr()->andX(
            $q->expr()->eq('IDENTITY(s.contactclient)', (int) $id),
            $q->expr()->eq('e.type', ':type')
        );

        if ($fromDate) {
            $expr->add(
                $q->expr()->gte('e.dateAdded', ':fromDate')
            );
            $q->setParameter('fromDate', $fromDate);
        }

        $q->where($expr)
            ->setParameter('type', $type);

        return $q->getQuery()->getArrayResult();
    }
}
