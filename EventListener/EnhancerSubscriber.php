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

use MauticPlugin\MauticContactLedgerBundle\Model\EntryModel;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class EnhancerSubscriber.
 */
class EnhancerSubscriber implements EventSubscriberInterface
{
    /**
     * @var \MauticPlugin\MauticContactLedgerBundle\Model\EntryModel
     */
    protected $entryModel;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    protected $logger;

    /**
     * @param \MauticPlugin\MauticContactLedgerBundle\Model\EntryModel $entryModel
     * @param \Symfony\Bridge\Monolog\Logger                           $logger
     */
    public function __construct(EntryModel $entryModel, Logger $logger)
    {
        $this->entryModel = $entryModel;
        $this->logger     = $logger;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'mauticplugin.mautic_enhancer.enhancer_complete' => ['enhancerAttributionCheck', 0],
        ];
    }

    /**
     * @param $enhancerEvent
     */
    public function enhancerAttributionCheck($enhancerEvent)
    {
        $this->logger->debug('EnhancerSubscriber Responding to enhancer complete');
        $enhancer = $enhancerEvent->getEnhancer();
        if (method_exists($enhancer, 'getCostPerEnhancement')) {
            $lead     = $enhancerEvent->getLead();
            $campaign = $enhancerEvent->getCampaign();
            $enhancer = $enhancerEvent->getEnhancer();
            $cost     = $enhancer->getCostPerEnhancement();
            $this->entryModel->addEntry($lead, $campaign, $enhancer, 'enhanced', $cost);
        }
    }
}
