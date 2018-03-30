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
     * @param mixed $dollarValue
     *
     * @return string
     */
    public static function formatDollar($dollarValue)
    {
        return sprintf('%0.3f', floatval($dollarValue));
    }

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
            ->setDateAdded(new \DateTime())
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
     * @param Campaign  $campaign
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getCampaignRevenueChartData(Campaign $campaign, \DateTime $dateFrom, \DateTime $dateTo)
    {
        $chartData = ['labels' => [], 'datasets' => []];
        $labels = $costs = $revenues = $profits = [];

        $data = $this->getRepository()->getCampaignRevenueData($campaign, $dateFrom, $dateTo);

        if (!empty($data)) {
            $defaultDollars = self::formatDollar(0);
            $result         = array_shift($data);
            $resultDateTime = new \DateTime($result['label']);

            // iterate over range steps
            $labelDateTime = new \DateTime($dateFrom->format('Ymd'));
            while ($dateTo >= $labelDateTime) {
                $labels[] = $labelDateTime->format('M j, y');

                if ($labelDateTime == $resultDateTime) {
                    // record match
                    $costs[]    = self::formatDollar(-$result['cost']);
                    $revenues[] = self::formatDollar($result['revenue']);
                    $profits[]  = self::formatDollar($result['profit']);

                    // prep next entry
                    if (!empty($data)) {
                        $result         = array_shift($data);
                        $resultDateTime = new \DateTime($result['label']);
                    }
                } else {
                    $costs[]    = $defaultDollars;
                    $revenues[] = $defaultDollars;
                    $profits[]  = $defaultDollars;
                }

                $labelDateTime->modify('+1 day');
            }

            //undo change for inclusive filters
            $dateTo->modify('-1 second');
            $chartData = [
                'labels'   => $labels,
                'datasets' => [
                    [
                        'label'                     => 'Cost',
                        'data'                      => $costs,
                        'backgroundColor'           => 'rgba(204,51,51,0.1)',
                        'borderColor'               => 'rgba(204,51,51,0.8)',
                        'pointHoverBackgroundColor' => 'rgba(204,51,51,0.75)',
                        'pointHoverBorderColor'     => 'rgba(204,51,51,1)',
                    ],
                    [
                        'label'                     => 'Reveue',
                        'data'                      => $revenues,
                        'backgroundColor'           => 'rgba(51,51,51,0.1)',
                        'borderColor'               => 'rgba(51,51,51,0.8)',
                        'pointHoverBackgroundColor' => 'rgba(51,51,51,0.75)',
                        'pointHoverBorderColor'     => 'rgba(51,51,51,1)',
                    ],
                    [
                        'label'                     => 'Profit',
                        'data'                      => $profits,
                        'backgroundColor'           => 'rgba(51,204,51,0.1)',
                        'borderColor'               => 'rgba(51,204,51,0.8)',
                        'pointHoverBackgroundColor' => 'rgba(51,204,51,0.75)',
                        'pointHoverBorderColor'     => 'rgba(51,204,51,1)',
                    ],
                ],
            ];
        }

        return $chartData;
    }

    /**
     * @param Campaign $campaign
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getCampaignRevenueDatatableData(Campaign $campaign, \DateTime $dateFrom, \DateTime $dateTo)
    {
        $response = [];

        $results =  $this->getRepository()->getCampaignRevenueData($campaign, $dateFrom, $dateTo);

        foreach ($results as $result) {
            $result['label']   = \DateTime::createFromFormat('Ymd', $result['label'])->format('m/d/Y');
            $result['cost']    = self::formatDollar($result['cost']);
            $result['revenue'] = self::formatDollar($result['revenue']);
            $result['profit']  = self::formatDollar($result['profit']);
            $response[]        = $result;
        }

        return $response;
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    public function getDashboardRevenueWidgetData($params)
    {
        return $this->getRepository()->getDashboardRevenueWidgetData($params);
    }

    /**
     * @return bool|\Doctrine\ORM\EntityRepository|\MauticPlugin\MauticContactLedgerBundle\Entity\LedgerEntryRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('MauticContactLedgerBundle:LedgerEntry');
    }
}
