<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic Community
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactLedgerBundle\Tests\Entity;

use Mautic\CampaignBundle\Entity\Campaign;
use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\MauticContactLedgerBundle\Entity\LedgerEntry;

/**
 * Class EntryTest.
 */
class LedgerEntryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LedgerEntry
     */
    protected $entry;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->entry = new LedgerEntry();
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        unset($this->entry);
    }

    public function stringProvider()
    {
        return [
            ['testing'],
            ['\\n'],
            [null],
            [''],
            ['words and space'],
        ];
    }

    public function integerProvider()
    {
        return [
            [2],
            [3456],
            [0],
            [-3],
        ];
    }

    public function decimalProvider()
    {
        return [
            ['90.22'],
            ['0.001'],
            ['-0.001'],
            ['3.030303030303'],
        ];
    }

    /**
     * @dataProvider stringProvider
     */
    public function testActivity($activity) //, $expectPass = true, $expectException = false)
    {
        $setGetActivity = $this->entry->setActivity($activity)->getActivity();

        $this->assertEquals($activity, $setGetActivity, 'Entry()->activity is behaving abnormally');
    }

    /**
     * @dataProvider stringProvider
     */
    public function testBundleName($bundleName)
    {
        $setGetBundleName = $this->entry->setBundleName($bundleName)->getBundleName();

        $this->assertEquals($bundleName, $setGetBundleName, 'Entry()->bundleName is behaving abnormally');
    }

    public function testCampaign()
    {
        $testId   = rand(1, 1000);
        $campaign = $this->createMock(Campaign::class);
        $campaign->expects($this->any())
            ->method('getId')
            ->willReturn($testId);
        $setGetCampaign = $this->entry->setCampaign($campaign)->getCampaign();

        $this->assertEquals($campaign, $setGetCampaign, 'Entry()->campaign is behaving abnormally');
        $this->assertEquals($testId, $this->entry->getCampaignId(), 'Entry()->campaign is behaving abnormally');
    }

    public function testCampaignId()
    {
        $testId           = rand(1, 1000);
        $setGetCampaignId = $this->entry->setCampaignId($testId)->getCampaignId();
        $this->assertEquals($testId, $setGetCampaignId, 'Entry()->campaign is behaving abnormally');

        $campaign = $this->createMock(Campaign::class);
        $campaign->method('getId')->willReturn($testId);
        $setGetCampaignId = $this->entry->setCampaignId($campaign)->getCampaignId();
        $this->assertEquals($testId, $setGetCampaignId, 'Entry()->campaign is behaving abnormally');

        $this->expectException(\InvalidArgumentException::class);
        $this->entry->setCampaignId('bad arg');
    }

    /**
     * @dataProvider stringProvider
     *
     * @param $className
     */
    public function testClassName($className)
    {
        $setGetClassName = $this->entry->setClassName($className)->getClassName();
        $this->assertEquals($className, $setGetClassName, 'Entry()->className is behaving abnormally');
    }

    public function testContact()
    {
        $testId  = rand(1, 1000);
        $contact = $this->createMock(Lead::class);
        $contact->expects($this->any())
            ->method('getId')
            ->willReturn($testId);
        $setGetContact = $this->entry->setContact($contact)->getContact();

        $this->assertEquals($contact, $setGetContact, 'Entry()->contact is behaving abnormally');
        $this->assertEquals($testId, $this->entry->getContactId(), 'Entry()->contact is behaving abnormally');
    }

    public function testContactId()
    {
        $testId          = rand(1, 1000);
        $setGetContactId = $this->entry->setContactId($testId)->getContactId();
        $this->assertEquals($testId, $setGetContactId, 'Entry()->contact is behaving abnormally');

        $contact = $this->createMock(Lead::class);
        $contact->method('getId')->willReturn($testId);
        $setGetContactId = $this->entry->setContactId($contact)->getContactId();
        $this->assertEquals($testId, $setGetContactId, 'Entry()->contact is behaving abnormally');

        $this->expectException(\InvalidArgumentException::class);
        $this->entry->setContactId('bad arg');
    }

    /**
     * @dataProvider decimalProvider
     *
     * @param $cost
     */
    public function testCost($cost)
    {
        $setGetCost = $this->entry->setCost($cost)->getCost();

        $this->assertEquals($cost, $setGetCost, 'Entry()->cost is behaving abnormally');
    }

    public function testDateAdded()
    {
        $dateAdded       = new \DateTime();
        $setGetDateAdded = $this->entry->setDateAdded($dateAdded)->getDateAdded();

        $this->assertEquals($dateAdded, $setGetDateAdded, 'Entry()->dateAdded is behaving abnormally');
    }

    /**
     * @dataProvider stringProvider
     */
    public function testMemo($memo)
    {
        $setGetMemo = $this->entry->setMemo($memo)->getMemo();

        $this->assertEquals($memo, $setGetMemo, 'Entry()->memo is behaving abnormally');
    }

    /**
     * @dataProvider integerProvider
     */
    public function testObjectId($objectId)
    {
        $setGetObjectId = $this->entry->setObjectId($objectId)->getObjectId();

        $this->assertEquals($objectId, $setGetObjectId, 'Entry()->objectId is behaving abnormally');
    }

    /**
     * @dataProvider decimalProvider
     */
    public function testRevenue($revenue)
    {
        $setGetRevenue = $this->entry->setRevenue($revenue)->getRevenue();

        $this->assertEquals($revenue, $setGetRevenue, 'Entry()->revenue is behaving abnormally');
    }
}
