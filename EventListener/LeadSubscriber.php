<?php

namespace MauticPlugin\MauticContactLedgerBundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Event\LeadEvent;
use Mautic\LeadBundle\LeadEvents;
use MauticPlugin\MauticContactLedgerBundle\Model\EntryModel;

/**
 * Class LeadSubscriber
 */
class LeadSubscriber extends CommonSubscriber
{
    /**
     * @return array[]
     */
    public static function getSubscribedEvents()
    {
        return [
            LeadEvents::LEAD_PRE_SAVE  => ['preSaveLeadAttributionCheck', -1],
        ];
    }

    /**
     * @var \MauticPlugin\MauticContactLedgerBundle\Model\EnrtyModel $entryModel
     */
    protected $entryModel;

    /**
     * @param \MauticPlugin\MauticContactLedgerBundle\Model\EnrtyModel $entryModel
     */
    public function __construct(EntryModel $entryModel)
    {
        $this->entryModel = $entryModel;
    }

    /**
     * @param \Mautic\LeadBundle\Event\LeadEvent $event
     */
    public function preSaveLeadAttributionCheck(LeadEvent $event)
    {
        $changes = $event->getChanges();
        $this->logger->warning('Checking for attribution changes');

        if (isset($changes['fields']) && isset($changes['fields']['attribution'])) {
            $this->logger->warning('Found a change! Send for processing');
            $routingInfo = $this->router->match($this->request->getPathInfo());
            $this->logger->warning('sending ' . print_r($routingInfo, true) . ' with event for processing');
            $this->entryModel->processAttributionChange($event, $routingInfo);
        }
    }
}
