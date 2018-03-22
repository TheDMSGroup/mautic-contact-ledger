<?php

namespace MauticPlugin\MauticContactLedgerBundle\Entity;

use Doctrine\DBAL\Query\QueryBuilder;
use Mautic\CampaignBundle\Entity\Campaign;
use Mautic\CoreBundle\Entity\CommonRepository;
use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\MauticContactLedgerBundle\Helper\
/**
 * Class EntryRepository extends {@see \Mautic\CoreBundle\Entity\CommonRepository}.
 */
class LedgerEntryRepository extends CommonRepository
{
   public function getCampaignNetRevenue(Campaign $campaign)
    {
        $dateField =
        $q = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select('')



        $q = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select('cl.leadlist_id, l.name')
            ->from(MAUTIC_TABLE_PREFIX.'campaign_leadlist_xref', 'cl')
            ->join('cl', MAUTIC_TABLE_PREFIX.'lead_lists', 'l', 'l.id = cl.leadlist_id');
        $q->where(
            $q->expr()->eq('cl.campaign_id', $id)
        );

        $lists   = [];
        $results = $q->execute()->fetchAll();

        foreach ($results as $r) {
            $lists[$r['leadlist_id']] = $r['name'];
        }

        return $lists;    }
}
