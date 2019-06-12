<?php

namespace MauticPlugin\MauticContactLedgerBundle\EventListener;

use Mautic\WebhookBundle\EventListener\WebhookSubscriberBase;
use MauticPlugin\MauticContactLedgerBundle\Event\ContactLedgerContextEvent;
use MauticPlugin\MauticContactLedgerBundle\Event\RevenueChangeEvent;
use MauticPlugin\MauticContactLedgerBundle\MauticContactLedgerEvents;

class RevenueEventSubscriber extends WebhookSubscriberBase
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            MauticContactLedgerEvents::REVENUE_CHANGE => 'onRevenueChange'
        ];
    }

    /**
     * @param ContactLedgerContextEvent $event
     */
    public function onRevenueChange(RevenueChangeEvent $event)
    {
        $payload = $event->getPayload();

        $webhookEvent = $this->getEventWebooksByType(MauticContactLedgerEvents::REVENUE_CHANGE);

        $this->webhookModel->queueWebhooks($webhookEvent, $payload);
    }
}
