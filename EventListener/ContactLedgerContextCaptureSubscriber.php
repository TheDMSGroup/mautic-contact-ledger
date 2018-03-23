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
use MauticPlugin\MauticContactLedgerBundle\MauticContactLedgerEvents;
use MauticPlugin\MauticContactLedgerBundle\Model\LedgerEntryModel;
use Symfony\Bridge\Monolog\Logger;

/**
 * Class LeadSubscriber.
 */
class ContactLedgerContextCaptureSubscriber extends CommonSubscriber
{
    /** @var LedgerEntryModel */
    protected $model;

    /** @var mixed */
    protected $context;

    /** @var Logger */
    protected $logger;

    /**
     * LeadSubscriber constructor.
     *
     * @param LedgerEntryModel $model
     * @param mixed            $context
     * @param Logger           $logger
     */
    public function __construct(LedgerEntryModel $model, $context = null, Logger $logger)
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
