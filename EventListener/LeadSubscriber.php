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
    protected $model;

    /**
     * @var ContactLedgerContextSubscriber
     */
    protected $context;

    /** @var Logger */
    protected $logger;

    /**
     * LeadSubscriber constructor.
     *
     * @param EntryModel $model
     * @param ContactLedgerContextSubscriber $context
     * @param Logger     $logger
     */
    public function __construct(EntryModel $model, ContactLedgerContextSubscriber $context, Logger $logger)
    {
        $this->model   = $model;
        $this->context = $context;
        $this->logger  = $logger;
    }

    /**
     * @return array[]
     */
    public static function getSubscribedEvents()
    {
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
            $this->logger->debug('Found an attribution change! Prepare for processing');

            $oldPrice = $changes['fields']['attribution'][0];
            $newPrice = $changes['fields']['attribution'][1];
            $price   = $newPrice - $oldPrice;

            $campaign = $this->context->getCampaign();
            $actor = $this->context->getActor();
            $type = $this->context->getType();

            if ('cost' === $type) {
                $this->model->addEntry($lead, $campaign, $actor, 'received', $price);
            } elseif ('revenue' === $type) {
                $this->model->addEntry($lead, $campaign, $actor, 'converted', null, $price);
            } else {
                $this->model->addEntry($lead, $campaign, $actor, 'notated', null, null, $price);
            }

            //Lead has nuo beeb saved yet?
            unset($changes['fields']['attribution']);
            $lead->setChanges($changes);
        }
    }
}
