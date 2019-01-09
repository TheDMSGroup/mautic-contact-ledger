<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Digital Media Solutions, LLC
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactLedgerBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class ChartDataAlterEvent.
 */
class ChartDataAlterEvent extends Event
{
    /**
     * @var string
     */
    protected $chartName;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $params;

    /**
     * ChartDataAlterEvent constructor.
     *
     * @param array  $params
     * @param string $context
     */
    public function __construct(
        $chartName,
        $params,
        $data
    ) {
        $this->chartName = $chartName;
        $this->data      = $data;
        $this->params    = $params;
    }

    /**
     * @return string
     */
    public function getChartName()
    {
        return $this->chartName;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
}
