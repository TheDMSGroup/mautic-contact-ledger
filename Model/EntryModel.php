<?php

namespace MauticPlugin\MauticContactLedgerBundle\Model;

use Mautic\LeadBundle\Entity\Lead;
use Mautic\CoreBundle\Model\AbstractCommonModel;
use MauticPlugin\MauticContactLedgerBundle\Entity\Entry;

/**
 * class EntryModel extends {@see \Mautic\CoreBundle\Model\AbstractCommonModel}
 */
class EntryModel extends AbstractCommonModel
{
    /**
     * @return \MauticPlugin\MauticContactLedgerBundle\Entity\EntryRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('MauticContactLedgerBundle:Entry');
    }

    /**
     * @param object $object
     *
     * @return array
     */
    protected function getActorFromObject($object)
    {
        $entryActor = [null, null, null];

        if (is_object($actor)) {
            $entryActor[2] = $actor->getId();
            $pathParts = explode('\\', get_class($actor));
            $entryActor[1] = array_pop($pathParts);
            foreach($pathParts as $pathPart) {
                if (strstr($pathPart, 'Bundle')) {
                    $entryActor[0] = $pathPart;
                    break;
                }
            }
        }

        return $entryActor;
    }

    /**
     * @param array $array
     *
     * @return array
     */
    protected function getActorFromArray(array $array)
    {
        $entryActor = [null, null, null];

        if (is_array($actor)) {
            $entryActor[2] = array_pop($actor); //id
            $entryActor[1] = array_pop($actor); //Class
            if (!empty($actor)) {
                $entryActor[0] = array_pop($actor); //Bundle
            } else { //the hard way
                foreach (get_declared_classes() as $namespaced) {
                    $pathParts = explode('\\', $namespaced);
                    $className = array_pop($pathParts);
                    if ($className === $entryActor[1]) {
                        foreach($pathParts as $pathPart) {
                            if (strstr($pathPart, 'Bundle')) {
                                $entryActor[0] = $pathPart;
                                break 2;
                            }
                        }
                    }
                }
            }
        }

        return $entryActor;
    }

    /**
     * @param \Mautic\LeadBundle\Entity\Lead            $lead       target of transaction
     * @param \Mautic\CampaignBundle\Entity\Campaign    $campaign   campaign
     * @param array|object                              $actor      [Class, id] or object that acted
     * @param string                                    $activity   cause for transaction
     * @param string|float                              $amount     decimal dollar amount of tranaction
     */
    public function writeRevenue(Lead $lead, Campaign $campaign, $actor, $activity, $amount)
    {
        $this->addEntry($lead, $campaign, $actor, $activity, null, $amount);
    }

    /**
     * @param \Mautic\LeadBundle\Entity\Lead            $lead       target of transaction
     * @param \Mautic\CampaignBundle\Entity\Campaign    $campaign   campaign
     * @param array|object                              $actor      [Class, id] or object that acted
     * @param string                                    $activity   cause for transaction
     * @param string|float                              $amount     decimal dollar amount of tranaction
     */
    public function writeCost(Lead $lead, Campaign $campaign, $actor, $activity, $amount)
    {
        $this->addEntry($lead, $campaign, $actor, $activity, $amount);
    }

    /**
     * @param \Mautic\LeadBundle\Entity\Lead            $lead       target of transaction
     * @param \Mautic\CampaignBundle\Entity\Campaign    $campaign   campaign
     * @param array|object                              $actor      [Class, id] or object that acted
     * @param string                                    $activity   cause for transaction
     * @param string|float                              $cost       decimal dollar amount of tranaction
     * @param string|float                              $revenue    decimal dollar amount of tranaction
     */
    protected function addEntry(Lead $lead, Campaign $campaign, $actor, $activity = 'unknown', $cost = null, $revenue = null)
    {
        $bundleName = $className = $actorId = null;

        if (is_array($actor)) {
            list($bundleName, $className, $actorId) = $this->getActorFromArray($actor);

        } elseif (is_object($actor)) {
            list($bundleName, $className, $actorId) = $this->getActorFromObject($actor);
        } else {
            list($bundleName, $className, $actorId) = array(null, null, -1);
        }

        $entry;
        $entry = $this->getRepository()
            ->getEntity(0)
            ->setContact($lead)
            ->setCampaign($campaign)
            ->setBundleName($bundleName)
            ->setClassName($className)
            ->setActorId($actorId)
            ->setActivity($activity)
            ->setCost($cost)
            ->setRevenue($revenue);

        $this->getRepository()->saveEntity($entry);
        $this->em->flush();
    }
}
