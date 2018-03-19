<?php

namespace MauticPlugin\MauticContactLedgerBundle\Model;


use Mautic\CoreBundle\Model\AbstractCommonModel;

class EntryModel extends AbstractCommonModel
{
    /**
     * {@inheritdoc}
     *
     * @return \Mautic\CoreBundle\Entity\AuditLogRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('MauticContactLedgerBundle:Entry');
    }

    /**
     * this is a revenue entry
     */
    public function enterDebit()
    {

    }

    /**
     * this is a cost entry
     */
    public function enterCredit()
    {

    }


    /**
     * @param Array $params
     *
     * @return mixed
     */
    public function getDataForRevenueWidget($params) {

        $data = [];
        $entries = $this->getRepository->getDashboardRevenueWidgetData($params);

        $data['entries'] = $entries;
        // do stuff to make it table friendly and add it to $data

        return $data;
    }
}
