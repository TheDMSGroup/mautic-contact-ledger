<?php

namespace MauticPlugin\MauticContactLedgerBundle\Tests\Entity;

use Mautic\CampaignBundle\Entity\Campaign;
use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\MauticContactLedgerBundle\Entity\Entry;
use PHPUnit\Framework\TestCase;

/**
 * Class EntryTest.
 */
class EntryTest extends TestCase
{
    /**
     * @var Entry
     */
    protected $entry;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->entry = new Entry();
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
        $campaign       = new Campaign();
        $setGetCampaign = $this->entry->setCampaign($campaign)->getCampaign();

        $this->assertEquals($campaign, $setGetCampaign, 'Entry()->campaign is behaving abnormally');
    }

    /**
     * @param $className
     */
    public function testClassName($className)
    {
        $setGetClassName = $this->entry->setClassName($className)->getClassName();

        $this->assertEquals($className, $setGetClassName, 'Entry()->className is behaving abnormally');
    }

    /**
     * @param $className
     */
    public function testContact($className)
    {
        $contact         = new Lead();
        $setGetClassName = $this->entry->setContact($contact)->getContact();

        $this->assertEquals($className, $setGetClassName, 'Entry()->contact is behaving abnormally');
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

        $this->assertEquals($revenue, $setGetCosr, 'Entry()->revenue is behaving abnormally');
    }
}
