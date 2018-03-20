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
use MauticPlugin\MauticContactLedgerBundle\Model\EntryModel;

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
        'campaign.revenue' => [],
    ];

    /**
     * @var EntryModel
     */
    protected $entryModel;

    /**
     * DashboardSubscriber constructor.
     *
     * @param EventModel $campaignEventModel
     */
    public function __construct(EntryModel $entryModel)
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
        if ($event->getType() == 'campaign.revenue') {
     //       if (!$event->isCached()) {
                $widget           = $event->getWidget();
                $params= $widget->getParams();
                $params['limit'] = ($widget->getHeight() - 200) / 35;
                $params['orderby'] = 'revenue';
                $data             = $this->entryModel->getDataForRevenueWidget($params);
                $data['height']   = $widget->getHeight();
                $data['page']     = 1;
                $data['maxPages'] = 10;
                $data['total']    = $data['summary']['count'];
                $event->setTemplateData(['data' => $data]);
            }

            $event->setTemplate('MauticContactLedgerBundle:Widgets:revenue.html.php');
            $event->stopPropagation();
      //  }
    }
}
