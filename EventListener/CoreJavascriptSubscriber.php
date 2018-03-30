<?php
/**
 * Created by PhpStorm.
 * User: nbush
 * Date: 3/29/18
 * Time: 11:08 AM.
 */

namespace MauticPlugin\MauticContactLedgerBundle\EventListener;

use Mautic\CoreBundle\CoreEvents;
use Mautic\CoreBundle\Event\BuildJsEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;

class CoreJavascriptSubscriber extends CommonSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::BUILD_MAUTIC_JS => [
                'injectJS', 0,
            ],
        ];
    }

    public function injectJS(BuildJsEvent $event)
    {
        $event->appendJs();
    }
}
