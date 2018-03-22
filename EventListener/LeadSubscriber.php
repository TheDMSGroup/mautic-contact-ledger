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
    /** @var \MauticPlugin\MauticContactLedgerBundle\Model\EntryModel */
    protected $model;

    /** @var mixed */
    protected $context;

    /** @var Logger */
    protected $logger;

    /**
     * LeadSubscriber constructor.
     *
     * @param EntryModel $model
     * @param mixed      $context
     * @param Logger     $logger
     */
    public function __construct(EntryModel $model, $context = null, Logger $logger)
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
            LeadEvents::LEAD_POST_SAVE => ['postSaveAttributionCheck', -1],
        ];
    }

    /**
     * @param \Mautic\LeadBundle\Event\LeadEvent $event
     */
    public function postSaveAttributionCheck(LeadEvent $event)
    {
        $lead    = $event->getLead();
        $changes = $lead->getChanges(false);

        if (isset($changes['fields']) && isset($changes['fields']['attribution'])) {
            $oldValue   = $changes['fields']['attribution'][0];
            $newValue   = $changes['fields']['attribution'][1];
            $difference = $newValue - $oldValue;

            $this->logger->debug('Found an attribution change of: '.$difference);

            $campaign = $this->context->getCampaign();
            $actor    = $this->context->getActor();
            $activity = $this->context->getActivity();

            if ($difference > 0) {
                $this->model->addEntry($lead, $campaign, $actor, $activity, null, $difference);
            } else {
                if ($difference < 0) {
                    $this->model->addEntry($lead, $campaign, $actor, $activity, abs($difference));
                }
            }

            unset($changes['fields']['attribution']);
            $lead->setChanges($changes);
        }
    }
}
