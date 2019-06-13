<?php

namespace MauticPlugin\MauticRevenueEventBundle\EventListener;

use Mautic\WebhookBundle\EventListener\WebhookSubscriberBase;
use MauticPlugin\MauticRevenueEventBundle\Event\RevenueEventContextEvent;
use MauticPlugin\MauticRevenueEventBundle\Event\RevenueChangeEvent;
use MauticPlugin\MauticRevenueEventBundle\MauticRevenueEventEvents;

class RevenueEventSubscriber extends WebhookSubscriberBase
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            MauticRevenueEventEvents::REVENUE_CHANGE => 'onRevenueChange'
        ];
    }

    /**
     * @param RevenueEventContextEvent $event
     */
    public function onRevenueChange(RevenueChangeEvent $event)
    {
        $payload = $event->getPayload();

        //trigger_error(json_encode($payload), E_USER_WARNING);

        $webhookEvent = $this->getEventWebooksByType(MauticRevenueEventEvents::REVENUE_CHANGE);

        $this->webhookModel->queueWebhooks($webhookEvent, $payload);
    }
}
