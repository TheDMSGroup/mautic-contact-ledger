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

use Doctrine\DBAL\Migrations\SkipMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Mautic\CoreBundle\Doctrine\AbstractMauticMigration;
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

        foreach (static::$migrations as $migration) {
            try {
                /** @var AbstractMauticMigration $migr */
                $migr = new $migration();
                $mirg->preUp($installedSchema);
                $mirg->up($installedSchema);
            } catch (\Exception $e) {
                if (!($e instanceof SkipMigrationException)) {
                    throw $e;
                }
            }
        }

        $queries = $installedSchema->toSql($platform);
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
