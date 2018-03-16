<?php

namespace MauticPlugin\MauticContactLedgerBundle\Tests\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Frameork\TestCase;

class EntryRepositoryTest extends TestCase
{
    /**
     * @var CommonRepository
     */
    private $repo;

    /**
     * @var QueryBuilder
     */
    private $qb;

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
}
