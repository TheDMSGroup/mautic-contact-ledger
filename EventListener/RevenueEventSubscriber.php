<?php

namespace MauticPlugin\MauticContactLedgerBundle\EventListener;

use Mautic\WebhookBundle\EventListener\WebhookSubscriberBase;
use MauticPlugin\MauticContactLedgerBundle\Event\ContactLedgerContextEvent;
use MauticPlugin\MauticContactLedgerBundle\MauticContactLedgerEvents;

class RevenueEventSubscriber extends WebhookSubscriberBase
{
    const EVENT_NAMES = [
        MauticContactLedgerEvents::CONTEXT_CREATE  => 'CREATE',
        MauticContactLedgerEvents::CONTEXT_CAPTURE => 'CAPTURE',
    ];

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            MauticContactLedgerEvents::CONTEXT_CREATE  => ['onContactLedgerAny', 0],
            MauticContactLedgerEvents::CONTEXT_CAPTURE => ['onContactLedgerAny', 0],
        ];
    }

    /**
     * @param ContactLedgerContextEvent $event
     */
    public function onDncPostDelete(ContactLedgerContextEvent $event, $eventType)
    {
        $memo    = $event->getMemo();
        $lead    = $event->getLead();

        $payload = [
            'memo'   => $memo,
            'lead'   => $lead,
            'action' => $this->getEventName($eventType),
        ];

        $webhookEvents = $this->getEventWebooksByType($eventType);

        $this->webhookModel->queueWebhooks($webhookEvents, $payload);
    }

    /**
     * @param $event
     *
     * @return string
     */
    private function getEventName($event)
    {
        return self::EVENT_NAMES[$event];
    }
}
