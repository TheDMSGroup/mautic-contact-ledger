<?php

namespace MauticPlugin\MauticContactLedgerBundle\Tests\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use MauticPlugin\MauticContactLedgerBundle\Entity\LedgerEntryRepository;

class LedgerEntryRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Mautic\CoreBundle\Entity\CommonRepository
     */
    private $repo;

    /**
     * @var \Doctrine\ORM\\QueryBuilder
     */
    private $qb;

    public function testGetTableAlias()
    {
        $value = $this->repo->getTableAlias();

        $this->assertEquals('cl', $value, 'LedgerEntry reposiort reporting unexpected table alias');
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
        $emMock = $this->createMock(EntityManager::class);

        $metaMock = $this->createMock(ClassMetadata::class);

        $this->repo = new LedgerEntryRepository($emMock, $metaMock);
        $this->qb   = new QueryBuilder($emMock);
    }
}
