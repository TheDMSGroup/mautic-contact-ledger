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

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Mautic\CoreBundle\CoreEvents;
use Mautic\CoreBundle\Event\CustomContentEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;

class CustomContentSubscriber extends CommonSubscriber
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * CustomContentSubscriber constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
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

    public function getContentInjection(CustomContentEvent $event)
    {
        switch ($event->getViewName()) {
            case 'MauticCampaignBundle:Campaign:details.html.php':
                $vars = $event->getVars();
                if ('tabs' === $event->getContext()) {
                    $tabTemplate = 'MauticContactLedgerBundle:Tabs:campaign_ledger_tabs.html.php';
                    $event->addTemplate(
                        $tabTemplate,
                        [
                            'tabData' => $vars,
                        ]
                    );
                }
                if ('tabs.content' === $event->getContext()) {
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
                }
                break;
        }
    }
}
