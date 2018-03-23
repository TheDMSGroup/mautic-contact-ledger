<?php
/**
 * Created by PhpStorm.
 * User: nbush
 * Date: 3/22/18
 * Time: 2:25 PM
 */

namespace MauticPlugin\MauticContactLedgerBundle\EventListener;

use Mautic\CoreBundle\Event\CustomContentEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\CoreBundle\CoreEvents;
use MauticPlugin\MauticContactLedgerBundle\Model\LedgerEntryModel;

/**
 * Class CustomContentSubscriber
 * @package MauticPlugin\MauticContactLedgerBundle\EventListener
 */
class CustomContentSubscriber extends CommonSubscriber
{
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
     * @var LedgerEntryModel
     */
    protected $model;

    /**
     * CustomContentSubscriber constructor.
     * @param LedgerEntryModel $ledgerEntryModel
     */
    public function __construct(LedgerEntryModel $ledgerEntryModel)
    {
        $this->model = $ledgerEntryModel;
    }

    /**
     * @param CustomContentEvent $customContentEvent
     * @return CustomContentEvent
     */
    public function getContentInjection(CustomContentEvent $customContentEvent)
    {
        $into = 'MauticCampaignBundle:Campaign:details.html.php';
        $at   = 'left.section.top';
        $vars = $customContentEvent->getVars();


        if ($customContentEvent->checkContext($into, $at) && isset($vars['campaign'])) {
            $chartData = $this->model->getCampaignChartData($vars['campaign']);

            $customContentEvent->addTemplate(
                'MauticContactLedgerBundle:Charts:campaign_revenue_chart.html.php',
                ['ledgerData' => $chartData]
            );
        }

        return $customContentEvent;
    }
}