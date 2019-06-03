<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Digital Media Solutions, LLC
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactLedgerBundle;

use Doctrine\DBAL\Migrations\Version;
use Doctrine\DBAL\Schema\Schema;
use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\PluginBundle\Bundle\PluginBundleBase;
use Mautic\PluginBundle\Entity\Plugin;

/**
 * Class MauticContactLedgerBundle.
 */
class MauticContactLedgerBundle extends PluginBundleBase
{
    private static $migrations = [Migrations\Version20190603144423::class];

    /**
     * Called by PluginController::reloadAction when the addon version does not match what's installed.
     *
     * @param Plugin        $plugin
     * @param MauticFactory $factory
     * @param null          $metadata
     * @param Schema        $installedSchema
     *
     * @throws \Exception
     */
    public static function onPluginUpdate(Plugin $plugin, MauticFactory $factory, $metadata = null, Schema $installedSchema = null)
    {
        $db             = $factory->getDatabase();
        $platform       = $db->getDatabasePlatform()->getName();
        $queries        = [];
        $fromVersion    = $plugin->getVersion();

        $table = $schema->getTable(MAUTIC_TABLE_PREFIX.'contactsource_stats');
        if (!$table->hasIndex('unique_dupe_prevent')) {
            $queries[] = "ALTER TABLE {$this->prefix}contactsource_stats ADD UNIQUE unique_dupe_prevent(campaign_id, contact_client_id, utm_source, date_added)";
        }

        $table = $schema->getTable(MAUTIC_TABLE_PREFIX.'contactclient_stats');
        if (!$table->hasIndex('unique_dupe_prevent')) {
            $queries[] = "ALTER TABLE {$this->prefix}contactclient_stats ADD UNIQUE unique_dupe_prevent(campaign_id, contact_client_id, utm_source, date_added)";
        }

        if (!empty($queries)) {
            $db->beginTransaction();
            try {
                foreach ($queries as $q) {
                    $db->query($q);
                }
                $db->commit();
            } catch (\Exception $e) {
                $db->rollback();
                throw $e;
            }
        }
    }
}
