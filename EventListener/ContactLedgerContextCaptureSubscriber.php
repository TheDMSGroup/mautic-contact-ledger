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
use MauticPlugin\MauticContactLedgerBundle\MauticContactLedgerEvents;
use MauticPlugin\MauticContactLedgerBundle\Model\LedgerEntryModel;

/**
 * Class LeadSubscriber.
 */
class ContactLedgerContextCaptureSubscriber extends CommonSubscriber
{
    /** @var LedgerEntryModel */
    protected $model;

    /** @var mixed */
    protected $context;

    /**
     * LeadSubscriber constructor.
     *
     * @param LedgerEntryModel $model
     * @param mixed            $context
     */
    public function __construct(LedgerEntryModel $model, $context = null)
    {
        $this->model   = $model;
        $this->context = $context;
    }

    /**
     * @return array[]
     */
    public static function getSubscribedEvents()
    {
        return [
            MauticContactLedgerEvents::CONTEXT_CAPTURE => ['contextCapture', 0],
        ];
    }

    /**
     * @param mixed $event
     */
    public function contextCapture($event)
    {
        $lead     = $this->context->getLead();
        $campaign = $this->context->getCampaign();
        $actor    = $this->context->getActor();
        $activity = $this->context->getActivity();

        $this->model->addEntry($lead, $campaign, $actor, $activity);
    }
}
