<?php

namespace MauticPlugin\MauticContactLedgerBundle\EventListener;

use Mautic\WebhookBundle\WebhookEvents;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\WebhookBundle\Event\WebhookBuilderEvent;
use MauticPlugin\MauticContactLedgerBundle\MauticContactLedgerEvents;

/**
 * Class RevenueEventWebhookSubscriber Registers the Webhook for Revenue Events.
 *
 * @package MauticPlugin\MauticContactLedgerBundle\EventListener
 */
class RevenueEventWebhookSubscriber extends CommonSubscriber
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            WebhookEvents::WEBHOOK_ON_BUILD => ['onWebhookBuild', 0],
        ];
    }

    /**
     * Register REVENUE_CHANGE Event Webhook.
     *
     * @param WebhookBuilderEvent $event
     */
    public function onWebhookBuild(WebhookBuilderEvent $event)
    {
        $event->addEvent(
            MauticContactLedgerEvents::REVENUE_CHANGE,
            [
                'label' => 'Lead Revenue Changed',
            ]
        );
    }
}
