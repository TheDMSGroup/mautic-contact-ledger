<?php

namespace MauticPlugin\MauticContactLedgerBundle\Model;

use Mautic\CampaignBundle\Entity\Campaign;
use Mautic\CoreBundle\Model\AbstractCommonModel;
use Mautic\LeadBundle\Event\LeadEvent;
use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\MauticContactLedgerBundle\Entity\Entry;

/**
 * class EntryModel
 */
class EntryModel extends AbstractCommonModel
{
    /**
     * @return \MauticPlugin\MauticContactLedgerBundle\Entity\EntryRepository
     */
    public function getEntryRepository()
    {
        return $this->em->getRepository('MauticPlugin\\MauticContactLedgerBundle\\Entity\\Entry');
    }

    /**
     * @param \Mautic\LeadBundle\Event\LeadEvent $event
     */
    public function processAttributionChange(LeadEvent $event, array $routingInfo = [])
    {
        $this->logger->warning('PROCESSING ' . strtoupper($event->getName()));

        $changes = $event->getChanges();
        if (isset($changes['fields']) && isset($changes['fields']['attribution'])) {

            $price = $changes['fields']['attribution'];
            $contact = $event->getLead();

            if (!$contact->getId()) { //new leads handled this way

                $actor = [null, null, null];
                $campaign = null;
                $action = 'contactBuy';

                if (isset($routingInfo['campaignId'])) {
                    $campaign = $this->em->getRepository('Mautic\\CampaignBundle\\Entity\\Campaign')->find($routingInfo['campaignId']);
                } else {
                    $logger->alert("Unable to properly log cost of new Lead, Campaign not found");
                    return false;
                }

                if (isset($routingInfo['sourceId'])) {
                    $actor = ['MauticContactSourceBundle','ContactSource', $routingInfo['sourceId']];
                } else {
                    $logger->alert("Unable to properly log cost of new Lead, ContactSource not found.");
                    return false;
                }
                $this->writeCost($contact, $campaign, $actor, $action, $price);
            }
        }
    }

    /**
     * @param object $object
     *
     * @return array
     */
    protected function getActorFromObject($object)
    {
        $entryObject = [null, null, null];

        if (is_object($object)) {
            $entryObject[2] = $object->getId();
            $pathParts = explode('\\', get_class($object));
            $entryObject[1] = array_pop($pathParts);
            foreach($pathParts as $pathPart) {
                if (strstr($pathPart, 'Bundle')) {
                    $entryObject[0] = $pathPart;
                    break;
                }
            }
        }

        return $entryObject;
    }

    /**
     * @param array $array
     *
     * @return array
     */
    protected function getActorFromArray(array $array)
    {
        $entryObject = [null, null, null];

        $entryObject[2] = array_pop($object); //id
        $entryObject[1] = array_pop($object); //Class
        if (!empty($object)) {
            $entryObject[0] = array_pop($object); //Bundle
        } else { //the hard way
            foreach (get_declared_classes() as $namespaced) {
                $pathParts = explode('\\', $namespaced);
                $className = array_pop($pathParts);
                if ($className === $entryObject[1]) {
                    foreach($pathParts as $pathPart) {
                        if (false !== strstr($pathPart, 'Bundle')) {
                            $entryObject[0] = $pathPart;
                            break 2;
                        }
                    }
                }
            }
        }
        return $entryObject;
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
     * @param array|object                              $object      [Class, id] or object that acted
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
        $bundleName = $className = $objectId = null;

        if (is_array($actor)) {
            list($bundleName, $className, $objectId) = $this->getActorFromArray($actor);

        } elseif (is_object($object)) {
            list($bundleName, $className, $objectId) = $this->getActorFromObject($actor);
        } else {
            list($bundleName, $className, $objectId) = array(null, null, -1);
        }

        $entry = $this->getRepository()->getEntity()
            ->setContact($lead)
            ->setCampaign($campaign)
            ->setBundleName($bundleName)
            ->setClassName($className)
            ->setObjectId($objectId)
            ->setActivity($activity)
            ->setCost($cost)
            ->setRevenue($revenue);

        $this->getRepository()->saveEntity($entry);
        $this->em->flush();
    }
}
