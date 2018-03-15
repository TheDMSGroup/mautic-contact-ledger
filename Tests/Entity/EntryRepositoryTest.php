<?php

namespace MauticPlugin\MauticContactLedgerBundle\Tests\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use MauticPlugin\MauticContactLedgerBundle\Entity\EntryRepository;
use PHPUnit\Framework\TestCase;

class EntryRepositoryTest extends TestCase
{
    /**
     * @var \Mautic\CoreBundle\Entity\CommonRepository
     */
    private $repo;

    /**
     * @var QueryBuilder
     */
    private $qb;

    public function testGetTableAlias()
    {
    }

    public function testGetContactCost()
    {
    }

    public function testGetContactRevenue()
    {
    }

    public function testGetContactLedger()
    {
    }

    protected function setUp()
    {
        $emMock = $this->getMockBuilder(EntityManager::class)
            ->setMethods(['none'])
            ->disableOriginalConstructor()
            ->getMock();

        $metaMock = $this->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->repo = new EntryRepository($emMock, $metaMock);
        $this->qb   = new QueryBuilder($emMock);
    }
}
