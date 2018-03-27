<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic Community
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactLedgerBundle\EventListener;

use Mautic\CampaignBundle\Entity\Campaign;
use Mautic\CoreBundle\CoreEvents;
use Mautic\CoreBundle\Event\CustomContentEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\DashboardBundle\Model\DashboardModel;
use MauticPlugin\MauticContactLedgerBundle\Model\LedgerEntryModel;

/**
 * Class CustomContentSubscriber.
 */
class CustomContentSubscriber extends CommonSubscriber
{
    /**
     * @var LedgerEntryModel
     */
    protected $ledgerEntryModel;

    /**
     * @var DashboardModel
     */
    protected $dashboardModel;

    /**
     * CustomContentSubscriber constructor.
     *
     * @param LedgerEntryModel $ledgerEntryModel
     * @param DashboardModel   $dashboardModel
     */
    public function __construct(LedgerEntryModel $ledgerEntryModel, DashboardModel $dashboardModel)
    {
        $this->ledgerEntryModel = $ledgerEntryModel;
        $this->dashboardModel   = $dashboardModel;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::VIEW_INJECT_CUSTOM_CONTENT => ['getContentInjection', 0],
        ];
    }

    /**
     * @param CustomContentEvent $customContentEvent
     *
     * @return CustomContentEvent
     */
    public function getContentInjection(CustomContentEvent $customContentEvent)
    {
        /** @var \DateTime[] $dateRange */
        $dateRange = $this->request->request->get('daterange', []);
        /** @var array $vars */
        $vars = $customContentEvent->getVars();

        /** @var mixed $chartData */
        $chartData = null;

        /** @var string $chartTemplate */
        $chartTemplate = '';

        if (empty($dateRange)) {
            $dateRange = $this->dashboardModel->getDefaultFilter();
            $dateFrom  = $dateRange['date_from'] = $dateRange['dateFrom'];
            $dateTo    = $dateRange['date_to'] = $dateRange['dateTo'];
        } else {
            $dateFrom = $dateRange['dateFrom'] = new \DateTime($dateRange['date_from']);
            $dateTo   = $dateRange['dateTo'] = new \DateTime($dateRange['date_to']);
        }

        switch ($customContentEvent->getViewName()) {
            case 'MauticCampaignBundle:Campaign:details.html.php':
                if ('left.section.top' === $customContentEvent->getContext()) {
                    if (isset($vars['campaign'])) {
                        $chartTemplate = 'MauticContactLedgerBundle:Charts:campaign_revenue_chart.html.php';
                        $chartData     = $this->ledgerEntryModel->getForRevenueChartData(
                            $vars['campaign'],
                            $dateFrom,
                            $dateTo
                        );
                    }
                    $customContentEvent->addTemplate($chartTemplate, ['CampaignRevenueData' => $chartData]);
                }
                break;
            //default:
        }

        return true;
    }
}
