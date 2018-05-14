<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic Community
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactLedgerBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;

/**
 * Class LedgerEntryRepository.
 */
class CampaignSourceStatsRepository extends CommonRepository
{
    /**
     * @param $dollarValue
     *
     * @return string
     */
    public static function formatDollar($dollarValue)
    {
        return sprintf('%19.4f', floatval($dollarValue));
    }

    /**
     * @return string
     */
    public function getTableAlias()
    {
        return 'css';
    }

    /**
     * Gets the ID of the latest ID.
     *
     * @return int
     */
    public function getMaxId()
    {
        $result = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select('max(id) AS id')
            ->from(MAUTIC_TABLE_PREFIX.'contact_ledger_campaign_source_stats', 'css')
            ->execute()->fetchAll();

        return $result[0]['id'];
    }

    /**
     * Gets the ID of the latest ID.
     *
     * @return int
     */
    public function getLastEntity()
    {
        $entity = null;
        $result = $this->getMaxId();

        if(isset($result))
        {
            $entity = $this->getEntity($result);
        }

        return $entity;
    }
}
