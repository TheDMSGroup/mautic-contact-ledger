<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactLedgerBundle\EventListener;

use Mautic\DashboardBundle\Event\WidgetDetailEvent;
use Mautic\DashboardBundle\EventListener\DashboardSubscriber as MainDashboardSubscriber;
use MauticPlugin\MauticContactLedgerBundle\Model\LedgerEntryModel;

/**
 * Class DashboardSubscriber.
 */
class DashboardSubscriber extends MainDashboardSubscriber
{
    /**
     * Define the name of the bundle/category of the widget(s).
     *
     * @var string
     */
    protected $bundle = 'campaign';

    /**
     * Define the widget(s).
     *
     * @var string
     */
    protected $types = [
        'campaign.revenue'        => [],
        'campaign.source.revenue' => [],
    ];

    /**
     * @var LedgerEntryModel
     */
    protected $entryModel;

    /**
     * DashboardSubscriber constructor.
     *
     * @param LedgerEntryModel $entryModel
     */
    public function __construct(LedgerEntryModel $entryModel)
    {
        $this->entryModel = $entryModel;
    }

    /**
     * Set a widget detail when needed.
     *
     * @param WidgetDetailEvent $event
     */
    public function onWidgetDetailGenerate(WidgetDetailEvent $event)
    {
        //       if (!$event->isCached()) {
        $widget = $event->getWidget();
        if ($widget->getHeight() < 330) {
            $widget->setHeight(330);
        }
        $params = $widget->getParams();
        // check date params and set defaults if not exist
        if (!isset($params['dateTo']) || !$params['dateTo'] instanceof \DateTime) {
            $params['dateTo'] = new \DateTime();
        }
        if (!isset($params['dateFrom']) || !$params['dateFrom'] instanceof \DateTime) {
            $params['dateFrom'] = $params['dateTo']->modify('-1 day');
        }

        $data['params'] = $params;
        $data['height'] = $widget->getHeight();
        $event->setTemplateData(['data' => $data]);

        if ('campaign.revenue' == $event->getType()) {
            $event->setTemplate('MauticContactLedgerBundle:Widgets:revenue.html.php');
        }

        if ('campaign.source.revenue' == $event->getType()) {
            $event->setTemplate('MauticContactLedgerBundle:Widgets:sourceRevenue.html.php');
        }

        $event->stopPropagation();
    }
}