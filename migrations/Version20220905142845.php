<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220905142845 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates table substance';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable("substance");
        $table->addColumn("ulid", "guid")->setNotnull(true)->setComment("(DC2Type:ulid)");
        $table->addColumn("substance_type", "string")->setNotnull(true)->setLength(255);
        $table->addColumn("short_name", "string")->setNotnull(true)->setLength(50);
        $table->addColumn("long_name", "string")->setNotnull(true)->setLength(255);
        $table->setPrimaryKey(["ulid"]);
        $table->addUniqueIndex(["short_name"], "UNIQ_E481CB193EE4B093");
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable("substance");
    }
}
