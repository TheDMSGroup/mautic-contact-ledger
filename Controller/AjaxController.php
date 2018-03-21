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
use Mautic\CoreBundle\Helper\InputHelper;
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
        $params['dateFrom'] = $_SESSION['_sf2_attributes']['mautic.dashboard.date.from'];
        $params['dateTo']   = $_SESSION['_sf2_attributes']['mautic.dashboard.date.to'];
        //$params['limit'] = 1000; // just in case we want to set this, or use a config parameter

        $entryModel      = $this->get('mautic.contactledger.model.entry');
        $ledgerRepo      = $entryModel->getEntryRepository();
        $data            = $ledgerRepo->getDashboardRevenueWidgetData($params);
        $data['columns'] = [
            $this->translator->trans('mautic.contactledger.dashboard.revenue.header.active'),
            $this->translator->trans('mautic.contactledger.dashboard.revenue.header.name'),
            $this->translator->trans('mautic.contactledger.dashboard.revenue.header.received'),
            $this->translator->trans('mautic.contactledger.dashboard.revenue.header.converted'),
            $this->translator->trans('mautic.contactledger.dashboard.revenue.header.revenue'),
            $this->translator->trans('mautic.contactledger.dashboard.revenue.header.cost'),
            $this->translator->trans('mautic.contactledger.dashboard.revenue.header.gm'),
            $this->translator->trans('mautic.contactledger.dashboard.revenue.header.margin'),
            $this->translator->trans('mautic.contactledger.dashboard.revenue.header.ecpm'),
        ];
        $data            = UTF8Helper::fixUTF8($data);

        return $this->sendJsonResponse($data);
    }
}