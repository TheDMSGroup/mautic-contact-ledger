<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic Community
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactLedgerBundle\Model;

use DateTime;
use Mautic\CampaignBundle\Entity\Campaign;
use Mautic\CoreBundle\Model\AbstractCommonModel;
use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\MauticContactLedgerBundle\Entity\LedgerEntry;

/**
 * class LedgerEntryModel.
 */
class LedgerEntryModel extends AbstractCommonModel
{
    /**
     * @param Lead              $lead
     * @param Campaign|null     $campaign
     * @param array|object|null $actor
     * @param string            $activity
     * @param string|float|null $cost
     * @param string|float|null $revenue
     * @param string|null       $memo
     */
    public function addEntry(
        Lead $lead,
        Campaign $campaign = null,
        $actor = null,
        $activity = null,
        $cost = null,
        $revenue = null,
        $memo = null
    ) {
        $bundleName = $className = $objectId = null;

        if (is_array($actor)) {
            list($bundleName, $className, $objectId) = $this->getActorFromArray($actor);
        } elseif (is_object($actor)) {
            list($bundleName, $className, $objectId) = $this->getActorFromObject($actor);
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
            ->setMemo($memo)
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
     * @param Campaign $campaign
     * @param DateTime $dateFrom
     * @param DateTime $dateTo
     *
     * @return array
     */
    public function getCampaignChartData(Campaign $campaign, DateTime $dateFrom, DateTime $dateTo)
    {
        return $this->getRepository()->getCampaignChartData($campaign, $dateFrom, $dateTo);
    }

    /**
     * @return \MauticPlugin\MauticContactLedgerBundle\Entity\LedgerEntryRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('MauticContactLedgerBundle:LedgerEntry');
    }
}
