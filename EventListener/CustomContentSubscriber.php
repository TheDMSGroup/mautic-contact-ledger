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

use Mautic\CoreBundle\CoreEvents;
use Mautic\CoreBundle\Event\CustomAssetsEvent;
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
     * @param LedgerEntryModel $ledgerEntryModel
     * @param DashboardModel $dashboardModel
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
            CoreEvents::VIEW_INJECT_CUSTOM_ASSETS  => ['getAssetInjection', 0]
        ];
    }

    /**
     * @param CustomAssetsEvent $event
     *
     * @return CustomAssetsEvent
     */
    public function getAssetInjection(CustomAssetsEvent $event)
    {
        $location = $this->router->getContext()->getPathInfo();

        $this->logger->warning("at $location");
        if (preg_match('#campaigns/view/\d+$#', $location)) {
            $event->addScript('plugins/MauticContactLedgerBundle/Assets/js/datatables.min.js', 'bodyClose');
            $event->addStylesheet('plugins/MauticContactLedgerBundle/Assets/css/datatables.min.css');
        }

        return $event;
    }

    /**
     * @param CustomContentEvent $event
     *
     * @return CustomContentEvent
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getContentInjection(CustomContentEvent $event)
    {
        /** @var \DateTime[] $dateRange */
        $dateRange = $this->request->request->get('daterange', []);

        if (empty($dateRange)) {
            $dateRange = $this->dashboardModel->getDefaultFilter();
            $dateFrom  = $dateRange['date_from'] = $dateRange['dateFrom'];
            $dateTo    = $dateRange['date_to'] = $dateRange['dateTo'];
        } else {
            $dateFrom = $dateRange['dateFrom'] = new \DateTime($dateRange['date_from']);
            $dateTo   = $dateRange['dateTo'] = new \DateTime($dateRange['date_to']);
        }

        /** @var array $vars */
        $vars = $event->getVars();

        /** @var mixed $chartData */
        $chartData = null;

        /** @var string $chartTemplate */
        $chartTemplate = '';

        switch ($event->getViewName()) {
            case 'MauticCampaignBundle:Campaign:details.html.php':
                if ('left.section.top' === $event->getContext()) {
                    if (isset($vars['campaign'])) {
                        $chartTemplate = 'MauticContactLedgerBundle:Charts:campaign_revenue_chart.html.php';
                        $chartData     = $this->ledgerEntryModel->getCampaignRevenueChartData(
                            $vars['campaign'],
                            $dateFrom,
                            $dateTo
                        );
                    }
                    $event->addTemplate($chartTemplate, [
                        'campaignRevenueChartData' => $chartData,
                    ]);
                }
                break;
            //default:
        }

        return $event;
    }
}
