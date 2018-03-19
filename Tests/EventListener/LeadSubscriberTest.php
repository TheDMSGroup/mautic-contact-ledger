<?php

namespace MauticPlugin\MauticContactLedgerBundle\Tests\EventListener;

use Mautic\CoreBundle\Tests\CommonMocks;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Event\LeadEvent;
use MauticPlugin\MauticContactLedgerBundle\EventListener\LeadSubscriber;
use MauticPlugin\MauticContactLedgerBundle\Model\EntryModel;
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

        $changes = [
            'fields'         => [
                'attribution' => [
                    '0' => '0',
                    '1' => '0.123',
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

        $entryModel = $this->getMockBuilder(EntryModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        // This method will be called exactly once
        // even though the onLeadPreSave was called twice for the same lead
        $entryModel->expects($this->once())
            ->method('processAttributionChange');

        $logger = new Logger('test');
        $subscriber = new LeadSubscriber($entryModel, $logger);

        $leadEvent = $this->getMockBuilder(LeadEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $leadEvent->expects($this->exactly(2))
            ->method('getLead')
            ->will($this->returnValue($lead));

        // $leadEvent->expects($this->exactly(2))
        //     ->method('getChanges')
        //     ->will($this->returnValue($changes));

        $subscriber->preSaveLeadAttributionCheck($leadEvent);
        $subscriber->preSaveLeadAttributionCheck($leadEvent);

    }
}
