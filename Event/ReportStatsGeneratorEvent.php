<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactLedgerBundle\Event;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\Event;


/**
 * Class ReportStatsGeneratorEvent.
 */
class ReportStatsGeneratorEvent extends Event
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $context;

    /**
     * @var array
     */
    protected $params;

    /**
     * @var array
     */
    protected $statsCollection;

    /**
     * ReportStatsGeneratorEvent constructor.
     *
     * @param array  $params
     * @param string $context
     */
    public function __construct(
        $em,
        $params,
        $context
    ) {
        $this->em      = $em;
        $this->context = $context;
        $this->params  = $params;
    }

    /**
     * @return QueryBuilder
     */
    public function getStatsCollection()
    {
        return $this->queryBuilder;
    }

    public function setStatsCollection($statsCollection)
    {
        if (is_array($statsCollection)) {
            $this->statsCollection = $statsCollection;

        } else {
            throw new \InvalidArgumentException(
                '$statsCollection must be an array.'
            );
        }
    }

    /**
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
}
