<?php

/*
 * @package     Mautic
 * @copyright   2018 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactLedgerBundle\Migrations;

use Doctrine\DBAL\Migrations\SkipMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Mautic\CoreBundle\Doctrine\AbstractMauticMigration;

class Version20190603144423 extends AbstractMauticMigration
{
    /**
     * @param Schema $schema
     *
     * @throws SkipMigrationException
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function preUp(Schema $schema)
    {
        $table = $schema->getTable(MAUTIC_TABLE_PREFIX.'contactsource_stats');
        if ($table->hasIndex('unique_dupe_prevent')) {
            throw new SkipMigrationException('Schema includes this migration');
        }

        return true;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("ALTER TABLE {$this->prefix}contactsource_stats ADD UNIQUE unique_dupe_prevent(campaign_id, contact_client_id, utm_source, date_added)");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schem)
    {
        $this->addSql("ALTER TABLE {$this->prefix}contactsource_stats DROP INDEX unique_dupe_prevent");
    }
}
