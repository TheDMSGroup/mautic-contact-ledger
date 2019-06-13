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

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\LeadBundle\Event\LeadEvent;
use Mautic\LeadBundle\LeadEvents;
use MauticPlugin\MauticContactLedgerBundle\Event\RevenueChangeEvent;
use MauticPlugin\MauticContactLedgerBundle\MauticContactLedgerEvents;
use MauticPlugin\MauticContactLedgerBundle\Model\LedgerEntryModel;
use Symfony\Bridge\Monolog\Logger;

/**
 * Class LeadSubscriber.
 */
class LeadSubscriber extends CommonSubscriber
{
    /** @var LedgerEntryModel */
    protected $model;

    /** @var ContactLedgerContextSubscriber */
    protected $context;

    /** @var Logger */
    protected $logger;

    /**
     * LeadSubscriber constructor.
     *
     * @param LedgerEntryModel                    $model
     * @param ContactLedgerContextSubscriber|null $context
     * @param Logger|null                         $logger
     */
    public function __construct(
        LedgerEntryModel $model,
        ContactLedgerContextSubscriber $context = null,
        Logger $logger = null
    ) {
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
        $includePast = false;
        $lead        = $event->getLead();
        $changes     = $lead->getChanges($includePast);
        if (!isset($changes['fields']) || !isset($changes['fields']['attribution'])) {
            $includePast = true;
            $changes     = $lead->getChanges($includePast);
        }
        if (isset($changes['fields']) && isset($changes['fields']['attribution'])) {
            $oldValue = $changes['fields']['attribution'][0];
            $newValue = $changes['fields']['attribution'][1];
            // Ensure this is the latest change, even if it came from the PastChanges array on the contact.
            if ($oldValue !== $newValue && $newValue === $lead->getAttribution()) {
                $difference = $newValue - $oldValue;

                // if ($this->logger) {
                //     $this->logger->debug('Found an attribution change of: '.$difference);
                // }

                $campaign = $this->context ? $this->context->getCampaign() : null;
                $actor    = $this->context ? $this->context->getActor() : null;
                $activity = $this->context ? $this->context->getActivity() : null;
                $memo     = $this->context ? $this->context->getMemo() : null;

                if ($difference > 0) {
                    $this->model->addEntry($lead, $campaign, $actor, $activity, null, $difference, $memo);
                } else {
                    if ($difference < 0) {
                        $this->model->addEntry($lead, $campaign, $actor, $activity, abs($difference), null, $memo);
                    }
                }

                if (!$includePast) {
                    // Prevent further events on this change.
                    unset($changes['fields']['attribution']);
                    $lead->setChanges($changes);
                }

                //Revenue Event Webhook(s)
                //TODO: check for webhooks and campaign enabled
                $revenueEventServiceOn = true;
                if ($revenueEventServiceOn) {
                    $thisCampaignSendsWebhooks = true;
                    if ($thisCampaignSendsWebhooks) {
                        $this->dispatchRevenueEventWebhook(
                            $campaign ? $campaign->getId() : 0,
                            $lead->getId(), //TODO: change to Event/Ledger ID?
                            $lead->getFieldValue('clickid'),
                            $newValue
                        );
                    }
                }
            }
        }
    }

    private function dispatchRevenueEventWebhook($cid, $refid, $clickid, $price)
    {
        $payload = [
            'cid'     => $cid,
            'refid'   => $refid,
            'clickid' => $clickid,
            'price'   => $price,
        ];

        $event = new RevenueChangeEvent($payload);

        //trigger_error(json_encode($payload), E_USER_WARNING);

        $this->dispatcher->dispatch(MauticContactLedgerEvents::REVENUE_CHANGE, $event);
    }
}
