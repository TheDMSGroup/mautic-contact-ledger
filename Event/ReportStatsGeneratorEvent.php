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
     * @param EntityManager
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
     * @return array
     */
    public function getStatsCollection()
    {
        return $this->statsCollection;
    }

    /**
     * @param $statsCollection
     *
     * @return $this
     */
    public function setStatsCollection($statsCollection)
    {
        if (is_array($statsCollection)) {
            $this->statsCollection = $statsCollection;

            return $this;
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

    /**
     * @param $params
     *
     * @return $this
     */
    public function setParams($params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }
}
