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
        // Get the API payload to test.
        $params['dateFrom'] = $this->request->getSession()->get('mautic.dashboard.date.from');
        $params['dateTo'] = $this->request->getSession()->get('mautic.dashboard.date.to');
        //$params['limit'] = 1000; // just in case we want to set this, or use a config parameter

        $entryModel = $this->get('mautic.contactledger.model.ledgerentry');
        $ledgerRepo = $entryModel->getRepository();
        $data       = $ledgerRepo->getDashboardRevenueWidgetData($params);
        $headers    = [
            'mautic.contactledger.dashboard.revenue.header.active',
            'mautic.contactledger.dashboard.revenue.header.id',
            'mautic.contactledger.dashboard.revenue.header.name',
            'mautic.contactledger.dashboard.revenue.header.received',
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
}
