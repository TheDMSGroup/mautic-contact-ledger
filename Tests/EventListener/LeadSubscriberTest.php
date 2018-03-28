<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic Community
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactLedgerBundle\Tests\EventListener;

use Mautic\CoreBundle\Tests\CommonMocks;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Event\LeadEvent;
use MauticPlugin\MauticContactLedgerBundle\EventListener\LeadSubscriber;
use MauticPlugin\MauticContactLedgerBundle\Model\LedgerEntryModel;
use Symfony\Bridge\Monolog\Logger;

/**
 * Class LeadSubsciberTest.
 */
class LeadSubscriberTest extends CommonMocks
{
    public function testOnLeadPostSaveWillNotProcessTheSameLeadTwice()
    {
        $lead = new Lead();

        $lead->setId(60);

        $lead->attribution = 0.123;

        $changes = [
            'fields'         => [
                'attribution' => [
                    0 => null,
                    1 => 0.123,
                ],
            ],
            'dateModified'   => [
                '0' => '2017-08-21T15:50:57+00:00',
                '1' => '2017-08-22T08:04:31+00:00',
            ],
            'dateLastActive' => [
                '0' => '2017-08-21T15:50:57+00:00',
                '1' => '2017-08-22T08:04:31+00:00',
            ],
        ];
        $lead->setChanges($changes);

        $entryModel = $this->getMockBuilder(LedgerEntryModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        // This method will be called exactly once
        // even though the onLeadPreSave was called twice for the same lead
        $entryModel->expects($this->once())
            ->method('addEntry');

        $logger = new Logger('test');
        // @todo - create context.
        $context    = null;
        $subscriber = new LeadSubscriber($entryModel, $context, $logger);

        $leadEvent = $this->getMockBuilder(LeadEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $leadEvent->expects($this->exactly(4))
            ->method('getLead')
            ->will($this->returnValue($lead));

        // $leadEvent->expects($this->exactly(2))
        //     ->method('getChanges')
        //     ->will($this->returnValue($changes));

        $subscriber->postSaveAttributionCheck($leadEvent);
        $subscriber->postSaveAttributionCheck($leadEvent);
        $subscriber->postSaveAttributionCheck($leadEvent);
        $subscriber->postSaveAttributionCheck($leadEvent);
    }
}
