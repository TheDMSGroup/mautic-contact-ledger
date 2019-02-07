<?php
/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Digital Media Solutions, LLC
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactLedgerBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Mautic\CoreBundle\CoreEvents;
use Mautic\CoreBundle\Event\CustomContentEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\DashboardBundle\Model\DashboardModel;
use MauticPlugin\MauticContactLedgerBundle\Model\LedgerEntryModel;
use Symfony\Component\HttpFoundation\Session\Session;

class CustomContentSubscriber extends CommonSubscriber
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var LedgerEntryModel
     */
    protected $ledgerEntryModel;

    /**
     * @var DashboardModel
     */
    protected $dashboardModel;

    /**
     * @var Session
     */
    protected $session;

    /**
     * CustomContentSubscriber constructor.
     *
     * @param EntityManager    $em
     * @param LedgerEntryModel $ledgerEntryModel
     * @param DashboardModel   $dashboardModel
     */
    public function __construct(
        EntityManager $em,
        LedgerEntryModel $ledgerEntryModel,
        DashboardModel $dashboardModel,
        Session $session
    ) {
        $this->em               = $em;
        $this->ledgerEntryModel = $ledgerEntryModel;
        $this->dashboardModel   = $dashboardModel;
        $this->session          = $session;
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
     * @param CustomContentEvent $event
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getContentInjection(CustomContentEvent $event)
    {
        switch ($event->getViewName()) {
            case 'MauticCampaignBundle:Campaign:details.html.php':

                $postDateRange = $this->request->request->get('daterange', []); // POST vars
                if (empty($postDateRange)) {
                    /** @var \DateTime[] $dateRange */
                    $sessionDateFrom = $this->session->get('mautic.daterange.form.from'); // session Vars
                    $sessionDateTo   = $this->session->get('mautic.daterange.form.to');
                    if (empty($sessionDateFrom) && empty($sessionDateTo)) {
                        $dateRange = $this->dashboardModel->getDefaultFilter(); // App Default setting
                        $dateFrom  = new \DateTime($dateRange['date_from']);
                        $dateTo    = new \DateTime($dateRange['date_to']);
                    } else {
                        $dateFrom = new \DateTime($sessionDateFrom);
                        $dateTo   = new \DateTime($sessionDateTo);
                    }
                } else {
                    // convert POST strings to DateTime Objects
                    $dateFrom = new \DateTime($postDateRange['date_from']);
                    $dateTo   = new \DateTime($postDateRange['date_to']);
                    $this->session->set('mautic.daterange.form.from', $postDateRange['date_from']);
                    $this->session->set('mautic.daterange.form.to', $postDateRange['date_to']);
                }

                $dateFrom->setTime(0, 0, 0);
                $dateTo->setTime(23, 59, 59);

                $vars              = $event->getVars();
                $vars['dateRange'] = ['dateFrom' => $dateFrom, 'dateTo' => $dateTo];

                switch ($event->getContext()) {
                    case 'tabs':
                        $tabTemplate = 'MauticContactLedgerBundle:Tabs:campaign_ledger_tabs.html.php';
                        $event->addTemplate(
                            $tabTemplate,
                            [
                                'tabData' => $vars,
                            ]
                        );
                        break;

                    case 'tabs.content':
                        $tabContentTemplate = 'MauticContactLedgerBundle:Tabs:campaign_sourcestats_tab_content.html.php';
                        $event->addTemplate(
                            $tabContentTemplate,
                            [
                                'tabData'  => $vars,
                                'campaign' => $vars['campaign'],
                            ]
                        );
                        $tabContentTemplate = 'MauticContactLedgerBundle:Tabs:campaign_clientstats_tab_content.html.php';
                        $event->addTemplate(
                            $tabContentTemplate,
                            [
                                'tabData'  => $vars,
                                'campaign' => $vars['campaign'],
                            ]
                        );
                        break;

                    case 'left.section.top':
                        /** @var mixed $chartData */
                        $chartData = null;
                        /** @var string $chartTemplate */
                        $chartTemplate = '';
                        if (isset($vars['campaign'])) {
                            $chartTemplate = 'MauticContactLedgerBundle:Charts:campaign_revenue_chart.html.php';
                            $chartData     = $this->ledgerEntryModel->getCampaignRevenueChartData(
                                $vars['campaign'],
                                $dateFrom,
                                $dateTo
                            );
                        }
                        $date_from = clone $dateFrom;
                        $date_to   = clone $dateTo;

                        // $action = $this->generateUrl('mautic_campaign_action', ['objectAction' => 'view', 'objectId' => $vars['campaign']]);
                        $dateRangeForm = $event->getDispatcher()->getContainer()->get('form.factory')->create(
                            'daterange',
                            ['date_from' => $date_from->format('Y-m-d'), 'date_to' => $date_to->format('Y-m-d')]
                        );

                        $event->addTemplate(
                            $chartTemplate,
                            [
                                'campaignRevenueChartData' => $chartData,
                                'dateRangeForm'            => $dateRangeForm->createView(),
                            ]
                        );
                        break;
                }
                break;
        }
    }
}
