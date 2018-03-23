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
use Mautic\CoreBundle\Event\CustomContentEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use MauticPlugin\MauticContactLedgerBundle\Model\LedgerEntryModel;

/**
 * Class CustomContentSubscriber.
 */
class CustomContentSubscriber extends CommonSubscriber
{
    /**
     * @var LedgerEntryModel
     */
    protected $model;

    /**
     * CustomContentSubscriber constructor.
     *
     * @param LedgerEntryModel $ledgerEntryModel
     */
    public function __construct(LedgerEntryModel $ledgerEntryModel)
    {
        $this->model = $ledgerEntryModel;
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
        $into = 'MauticCampaignBundle:Campaign:details.html.php';
        $at   = 'left.section.top';
        $vars = $customContentEvent->getVars();

        if ($customContentEvent->checkContext($into, $at)) {
            $dateTo   = new \DateTime('now');
            $dateFrom = new \DateTime('now');
            $dateFrom->sub(new \DateInterval('P1M'));

            $chartData = $this->model->getCampaignChartData($vars['campaign'], $dateFrom, $dateTo);

            $customContentEvent->addTemplate(
                'MauticContactLedgerBundle:Charts:campaign_revenue_chart.html.php',
                ['ledgerData' => $chartData]
            );
        }

        return $customContentEvent;
    }
}
