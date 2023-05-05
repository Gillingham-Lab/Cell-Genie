<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210818085056 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("antibody");
        $table->addUniqueIndex(["number"], "UNIQ_5C97C6B196901F54");
        $table->addColumn("rrid", "string")->setLength(255)->setNotnull(false);
        $table->addColumn("dilution", "text")->setNotnull(false);
        $table->addColumn("storage_temperature", "smallint")->setNotnull(false);
        $table->addColumn("clonality", "string")->setLength(255)->setNotnull(false);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("antibody");
        $table->dropIndex("UNIQ_5C97C6B196901F54");
        $table->dropColumn("rrid");
        $table->dropColumn("dilution");
        $table->dropColumn("storage_temperature");
        $table->dropColumn("clonality");
    }
}
