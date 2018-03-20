<?php

namespace MauticPlugin\MauticContactLedgerBundle\Model;

use DateTime;
use Mautic\CampaignBundle\Entity\Campaign;
use Mautic\CoreBundle\Model\AbstractCommonModel;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Event\LeadEvent;
use MauticPlugin\MauticContactLedgerBundle\Entity\LedgerEntry;

/**
 * class EntryModel.
 */
class EntryModel extends AbstractCommonModel
{
    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getEntryRepository()
    {
        return $this->em->getRepository('MauticPlugin\MauticContactLedgerBundle\Entity\LedgerEntry');
    }

    /**
     * @param LeadEvent $event
     * @param array     $routingInfo
     *
     * @return bool
     */
    public function processAttributionChange(LeadEvent &$event, array $routingInfo = [])
    {
        $this->logger->debug('PROCESSING '.strtoupper($event->getName()));

        $changes = $event->getChanges();
        if (isset($changes['fields']) && isset($changes['fields']['attribution'])) {
            $oldPrice = $changes['fields']['attribution'][0];
            $newPrice = $changes['fields']['attribution'][1];

            $price   = $newPrice - $oldPrice;
            $contact = $event->getLead();

            if (!$contact->getId()) { //new leads handled this way
                $actor    = [null, null, null];
                $campaign = null;
                $action   = 'contactBuy';

                if (isset($routingInfo['campaignId'])) {
                    /** @var Campaign $campaign */
                    $campaign = $this->em->getRepository('Mautic\\CampaignBundle\\Entity\\Campaign')->find(
                        $routingInfo['campaignId']
                    );
                } else {
                    $this->logger->alert('Unable to properly log cost of new Lead, Campaign not found');

                    return false;
                }

                if (isset($routingInfo['sourceId'])) {
                    $actor = ['MauticContactSourceBundle', 'ContactSource', $routingInfo['sourceId']];
                } else {
                    $this->logger->alert('Unable to properly log cost of new Lead, ContactSource not found.');

                    return false;
                }
                //TODO: Verify accurascy
                if ($price < 0) {
                    $price *= -1;
                    $this->addEntry($contact, $campaign, $actor, $action, $price);
                } elseif ($price > 0) {
                    $this->addEntry($contact, $campaign, $actor, $action, null, $price);
                }
            }
        }
    }

    /**
     * @param \Mautic\LeadBundle\Entity\Lead         $lead     target of transaction
     * @param \Mautic\CampaignBundle\Entity\Campaign $campaign campaign
     * @param array|object                           $actor    [Class, id] or object that acted
     * @param string                                 $activity cause for transaction
     * @param string|float                           $cost     decimal dollar amount of tranaction
     * @param string|float                           $revenue  decimal dollar amount of tranaction
     */
    public function addEntry(
        Lead $lead,
        Campaign $campaign,
        $actor,
        $activity = 'unknown',
        $cost = null,
        $revenue = null
    ) {
        $bundleName = $className = $objectId = null;

        if (is_array($actor)) {
            list($bundleName, $className, $objectId) = $this->getActorFromArray($actor);
        } elseif (is_object($actor)) {
            list($bundleName, $className, $objectId) = $this->getActorFromObject($actor);
        } else {
            list($bundleName, $className, $objectId) = [null, null, -1];
        }

        $entry = new LedgerEntry();
        $entry
            ->setDateAdded(new DateTime())
            ->setContact($lead)
            ->setCampaign($campaign)
            ->setBundleName($bundleName)
            ->setClassName($className)
            ->setObjectId($objectId)
            ->setActivity($activity)
            ->setCost($cost)
            ->setRevenue($revenue);
        $this->em->persist($entry);
    }

    /**
     * @param array $array
     *
     * @return array
     */
    protected function getActorFromArray(array $array)
    {
        $entryObject = [null, null, null];

        $entryObject[2] = array_pop($array); //id
        $entryObject[1] = array_pop($array); //Class
        if (!empty($array)) {
            $entryObject[0] = array_pop($array); //Bundle
        } else { //the hard way
            foreach (get_declared_classes() as $namespaced) {
                $pathParts = explode('\\', $namespaced);
                $className = array_pop($pathParts);
                if ($className === $entryObject[1]) {
                    foreach ($pathParts as $pathPart) {
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
     * @param object $object
     *
     * @return array
     */
    protected function getActorFromObject($object)
    {
        $entryObject = [null, null, null];

        if (is_object($object)) {
            $entryObject[2] = $object->getId();
            $pathParts      = explode('\\', get_class($object));
            $entryObject[1] = array_pop($pathParts);
            foreach ($pathParts as $pathPart) {
                if (strstr($pathPart, 'Bundle')) {
                    $entryObject[0] = $pathPart;
                    break;
                }
            }
        }

        return $entryObject;
    }


    /**
     * @param Array $params
     *
     * @return mixed
     */
    public function getDataForRevenueWidget($params) {

        $data = [];
        $entries = $this->getEntryRepository()->getDashboardRevenueWidgetData($params);

        $data['entries'] = $entries['financials'];
        $data['summary'] = $entries['summary'];
        // do stuff to make it table friendly and add it to $data

        return $data;
    }
}

// gm = rev-cost
// margin = gm/cost
// eCPM = gm per 1000 leads
// received =
