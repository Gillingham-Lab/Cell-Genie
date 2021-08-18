<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210818134731 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("antibody");
        $table->addColumn("usage", "string")->setLength(255)->setNotnull(false);

    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("antibody");
        $table->addUniqueIndex(["number"], "UNIQ_5C97C6B196901F54");
        $table->dropColumn("usage");

    }
}
