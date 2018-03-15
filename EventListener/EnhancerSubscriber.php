<?php

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
     * @param \MauticPlugin\MauticEnhancerBundle\Event\MauticEnhancerEvent $enhancerEvent
     */
    public function enhancerAttributionCheck($enhancerEvent)
    {
        $this->logger->warning('EnhancerSubcriber Responding to enhancer complete');
        $enhancer     = $enhancerEvent->getEnhancer();
        $enhancerCost = $enhancer->getCostPerEnhancement();
        if ($enhancerCost) {
            $lead     = $enhancerEvent->getLead();
            $campaign = $enhancerEvent->getCampaign();
            $enhancer = $enhancerEvent->getEnhancer();
            $this->entryModel->addEntry($lead, $campaign, $enhancer, 'enhacement', $enhancerCost);
        }
    }
}
