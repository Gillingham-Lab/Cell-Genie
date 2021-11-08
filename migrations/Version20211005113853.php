<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211005113853 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("experimental_condition");
        $table->addColumn("type", "string")->setLength(30)->setNotnull(true);
        $table->addColumn("config", "text")->setNotnull(true);
        $table->dropColumn("order");
        $table->addColumn("_order", typeName: "integer")->setNotnull(true)->setDefault(0);

        $table = $schema->getTable("experimental_measurement");
        $table->addColumn("type", "string")->setLength(30)->setNotnull(true);
        $table->addColumn("config", "text")->setNotnull(true);
        $table->dropColumn("order");
        $table->addColumn("_order", typeName: "integer")->setNotnull(true)->setDefault(0);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable("experimental_condition");
        $table->dropColumn("type");
        $table->dropColumn("config");
        $table->dropColumn("_order");
        $table->addColumn("order", typeName: "integer")->setNotnull(true)->setDefault(0);

        $table = $schema->getTable("experimental_measurement");
        $table->dropColumn("type");
        $table->dropColumn("config");
        $table->dropColumn("_order");
        $table->addColumn("order", typeName: "integer")->setNotnull(true)->setDefault(0);
    }
}
