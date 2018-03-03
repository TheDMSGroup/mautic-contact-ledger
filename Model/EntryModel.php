<?php

namespace MauticPlugin\MauticContactLedgerBundle\Model;

use Mautic\LeadBundle\Entity\Lead;
use Mautic\CoreBundle\Model\AbstractCommonModel;
use MauticPlugin\MauticContactLedgerBundle\Entity\Entry;
/**
 * class EntryModel extends {@see \Mautic\CoreBundle\Model\AbstractCommonModel}
 *
 * @package \MauticPlugin\MauticContactLedgerBundle\Model
 */
class EntryModel extends AbstractCommonModel
{
    /**
     * {@inheritdoc}
     *
     * @return \MauticPlugin\MauticContactLedgerBundle\Entity\EntryRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('MauticContactLedgerBundle:Entry');
    }
    
    
    public function buildActorFromObject($object_instance)
    {
        $class = get_class($object_instance);
        
    }
    
    public function buildActorFromIdClass(int $id, string $class)
    {
        if (!in_array($class, get_declared_classes())) {
            $a = true;
        }
    }

    protected function findBundle($class)
    {
            
    }

    /**
     * Writes a revenue (credit) to the ledger
     *
     * @param \Mautic\LeadBundle\Entity\Lead    $lead       target of transaction
     * @param string|float                      $amount     decimal dollar amount of tranaction
     * @param string                            $activity   cause for transaction
     * @param mixed                             $actor      [bundle, object, id] that acted
     */
    public function writeRevenue(Lead $lead, $amount, $activity, $actor)
    {
        if (is_object($actor)) {
            
        }
        elseif (is_array($actor)) {
            
        }
        
        $entry = $this->getRepository()->getEntity(0);
        $entry->setCost($amount)
            ->setContact($lead)
            ->setActivity($activity);
            
    }

    /**
     * Writes a cost (debit) to the ledger
     *
     * @param \Mautic\LeadBundle\Entity\Lead    $lead       target of transaction
     * @param string|float                      $amount     decimal dollar amount of tranaction
     * @param string                            $activity   cause for transaction
     * @param mixed[]                           $actor      [bundle, object, id] that acted
     */
    public function writeCost(Lead $lead, $amount, $activity, $actor)
    {

    }
    
    
}
