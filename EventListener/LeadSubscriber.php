<?php

namespace MauticPlugin\MauticContactLedgerBundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\LeadBundle\Event\LeadEvent;
use Mautic\LeadBundle\LeadEvents;
use MauticPlugin\MauticContactLedgerBundle\Model\EntryModel;
use Symfony\Bridge\Monolog\Logger;

/**
 * Class LeadSubscriber.
 */
class LeadSubscriber extends CommonSubscriber
{
    /**
     * @var \MauticPlugin\MauticContactLedgerBundle\Model\EntryModel
     */
    protected $entryModel;

    /** @var Logger */
    protected $logger;

    /**
     * LeadSubscriber constructor.
     *
     * @param EntryModel $entryModel
     * @param Logger     $logger
     */
    public function __construct(EntryModel $entryModel, Logger $logger)
    {
        $this->entryModel = $entryModel;
        $this->logger     = $logger;
    }

    /**
     * @return array[]
     */
    public static function getSubscribedEvents()
    {
        // @todo - Try FIELD_PRE_SAVE for a tighter focus?
        return [
            LeadEvents::LEAD_PRE_SAVE => ['preSaveLeadAttributionCheck', -1],
        ];
    }

    /**
     * @param \Mautic\LeadBundle\Event\LeadEvent $event
     */
    public function preSaveLeadAttributionCheck(LeadEvent $event)
    {
        $lead    = $event->getLead();
        $changes = $lead->getChanges(false);

        if (isset($changes['fields']) && isset($changes['fields']['attribution'])) {
            $this->logger->debug('Found an attribution change! Send for processing');
            $routingInfo = [];
            // @todo - Make $this->router work here?
            if (isset($this->router)) {
                $routingInfo = $this->router->match($this->request->getPathInfo());
                $this->logger->debug('sending '.print_r($routingInfo, true).' with event for processing');
            }
            $this->entryModel->processAttributionChange($event, $routingInfo);
            unset($changes['fields']['attribution']);
            $lead->setChanges($changes);
        }
    }
}
