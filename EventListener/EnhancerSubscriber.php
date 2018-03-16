<?php

namespace MauticPlugin\MauticContactLedgerBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use MauticPlugin\MauticEnhancerBundle\MauticEnhancerEvents;
use MauticPlugin\MauticEnhancerBundle\Event\MauticEnhancerEvent;
use MauticPlugin\MauticEnhancerBundle\Integration\NonFreeEnhancerInterface;
use MauticPlugin\MauticContactLedgerBundle\Model\EntryModel;
use Symfony\Bridge\Monolog\Logger;

/**
 * Class EnhancerSubscriber
 */
class EnhancerSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            MauticEnhancerEvents::ENHANCER_COMPLETED => [ 'enhancerAttributionCheck', 0]
        ];
    }

    /**
     * @var \MauticPlugin\MauticContactLedgerBundle\Model\EntryModel $entryModel
     */
    protected $entryModel;

    /**
     * @var \Symfony\Bridge\Monolog\Logger $logger
     */
    protected $logger;

    /**
     * @param \MauticPlugin\MauticContactLedgerBundle\Model\EntryModel $entryModel
     * @param  \Symfony\Bridge\Monolog\Logger $logger
     */
    public function __construct(EntryModel $entryModel, Logger $logger)
    {
        $this->entryModel = $entryModel;
        $this->logger = $logger;
    }

    /**
     * @param \MauticPlugh\MauticEnhancerBundle\Event\MauticEnhancerEvent $enhancerEvent
     */
    public function enhancerAttributionCheck(MauticEnhancerEvent $enhancerEvent)
    {
        $this->logger->warning('EnhancerSubcriber Responding to enhancer complete');
        $enhancer = $enhancerEvent->getEnhancer();
        if ($enhancer instanceof NonFreeEnhancerInterface) {
            $lead = $enhancerEvent->getLead();
            $campaign = $enhancerEvent->getCampaign();
            $enhancer = $enhancerEvent->getEnhancer();
            $this->entryModel->addEntry($lead, $campaign, $enhancer, 'enhacement',$enhancer->getCostPerEnhancement());
        }
    }

}
