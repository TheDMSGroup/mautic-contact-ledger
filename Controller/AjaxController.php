<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactLedgerBundle\Controller;

use Mautic\CoreBundle\Controller\AjaxController as CommonAjaxController;
use Mautic\CoreBundle\Controller\AjaxLookupControllerTrait;
use Mautic\CoreBundle\Helper\CacheStorageHelper;
use Mautic\CoreBundle\Helper\UTF8Helper;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AjaxController.
 */
class AjaxController extends CommonAjaxController
{
    use AjaxLookupControllerTrait;

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @throws \Exception
     */
    protected function globalRevenueAction(Request $request)
    {
        $cache = $this->get('Mautic\CoreBundle\Helper\CacheStorageHelper');
        if (!$data = $this->isAjaxDataCached('global-revenue-dashboard-widget', $cache)) {
            // Get the API payload to test.
            $params['dateFrom'] = $this->request->getSession()->get('mautic.dashboard.date.from');
            $params['dateTo']   = $this->request->getSession()->get('mautic.dashboard.date.to');
            //$params['limit'] = 1000; // just in case we want to set this, or use a config parameter

            $entryModel = $this->get('mautic.contactledger.model.ledgerentry');
            $ledgerRepo = $entryModel->getRepository();
            $data       = $ledgerRepo->getDashboardRevenueWidgetData($params, false);
            $cache->set('global-revenue-dashboard-widget', $data, 900);
        }
        $headers    = [
            'mautic.contactledger.dashboard.revenue.header.active',
            'mautic.contactledger.dashboard.revenue.header.id',
            'mautic.contactledger.dashboard.revenue.header.name',
            'mautic.contactledger.dashboard.revenue.header.received',
            'mautic.contactledger.dashboard.revenue.header.scrubbed',
            'mautic.contactledger.dashboard.revenue.header.declined',
            'mautic.contactledger.dashboard.revenue.header.converted',
            'mautic.contactledger.dashboard.revenue.header.revenue',
            'mautic.contactledger.dashboard.revenue.header.cost',
            'mautic.contactledger.dashboard.revenue.header.gm',
            'mautic.contactledger.dashboard.revenue.header.margin',
            'mautic.contactledger.dashboard.revenue.header.ecpm',
        ];
        foreach ($headers as $header) {
            $data['columns'][] = [
                'title' => $this->translator->trans($header),
            ];
        }
        $data = UTF8Helper::fixUTF8($data);

        return $this->sendJsonResponse($data);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @throws \Exception
     */
    protected function sourceRevenueAction(Request $request)
    {
        $cache = $this->get('Mautic\CoreBundle\Helper\CacheStorageHelper');
        if (!$data = $this->isAjaxDataCached('source-revenue-dashboard-widget', $cache)) {
            // Get the API payload to test.
            $params['dateFrom'] = $this->request->getSession()->get('mautic.dashboard.date.from');
            $params['dateTo']   = $this->request->getSession()->get('mautic.dashboard.date.to');
            //$params['limit'] = 1000; // just in case we want to set this, or use a config parameter

            $entryModel = $this->get('mautic.contactledger.model.ledgerentry');
            $ledgerRepo = $entryModel->getRepository();
            $data       = $ledgerRepo->getDashboardRevenueWidgetData($params, true);
            $cache->set('source-revenue-dashboard-widget', $data, 900);
        }
        $headers    = [
            'mautic.contactledger.dashboard.source-revenue.header.active',
            'mautic.contactledger.dashboard.source-revenue.header.id',
            'mautic.contactledger.dashboard.source-revenue.header.name',
            'mautic.contactledger.dashboard.source-revenue.header.sourceid',
            'mautic.contactledger.dashboard.source-revenue.header.sourcename',
            'mautic.contactledger.dashboard.source-revenue.header.received',
            'mautic.contactledger.dashboard.source-revenue.header.scrubbed',
            'mautic.contactledger.dashboard.source-revenue.header.declined',
            'mautic.contactledger.dashboard.source-revenue.header.converted',
            'mautic.contactledger.dashboard.source-revenue.header.revenue',
            'mautic.contactledger.dashboard.source-revenue.header.cost',
            'mautic.contactledger.dashboard.source-revenue.header.gm',
            'mautic.contactledger.dashboard.source-revenue.header.margin',
            'mautic.contactledger.dashboard.source-revenue.header.ecpm',
        ];
        foreach ($headers as $header) {
            $data['columns'][] = [
                'title' => $this->translator->trans($header),
            ];
        }
        $data = UTF8Helper::fixUTF8($data);

        return $this->sendJsonResponse($data);
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    protected function formatCurrency($value)
    {
        return sprintf('$%0.2f', floatval($value));
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    protected function datatablesAction(Request $request)
    {
        $response         = [];
        $response['data'] = [];

        switch ($request->query->get('which', 'default')) {
            case 'campaign-ledger':

                /** @var \Mautic\CampaignBundle\Model\CampaignModel $campaignModel */
                $campaignModel = $this->get('mautic.campaign.model.campaign');
                $campaign      = $campaignModel->getEntity($request->query->get('campaignId'));

                $dateFrom = new \DateTime($request->query->get('date_from'));
                $dateTo   = new \DateTime($request->query->get('date_to'));

                /** @var \MauticPlugin\MauticContactLedgerBundle\Model\LedgerEntryModel $ledgerEntry */
                $ledgerEntry      = $this->get('mautic.contactledger.model.ledgerentry');
                $response['data'] = $ledgerEntry->getCampaignRevenueDatatableData($campaign, $dateFrom, $dateTo);

                break;
            default:
        }

        return $this->sendJsonResponse($response);
    }

    /**
     * @param                    $cacheKey
     * @param CacheStorageHelper $cache
     *
     * @return bool|mixed
     */
    private function isAjaxDataCached($cacheKey, CacheStorageHelper $cache)
    {
        $data = $cache->get($cacheKey);
        if ($data) {
            return $data;
        } else {
            $cache->set($cacheKey, null, 900);

            return false;
        }
    }
}
