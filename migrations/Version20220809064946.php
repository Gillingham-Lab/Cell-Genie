<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220809064946 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("cell_culture");
        $table->addColumn("number", "string")->setLength(10)->setNotnull(true)->setDefault("???");

        $table = $schema->getTable("cell_aliquote");
        $table->addColumn("parent_aliquot_id", "integer")->setNotnull(false);
        $table->addForeignKeyConstraint("cell_aliquote", ["parent_aliquot_id"], ["id"], ["onDelete" => "SET NULL"], "FK_E2BD616398D0C6D0");
        $table->addIndex(["parent_aliquot_id"], "IDX_E2BD616398D0C6D0");
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("cell_culture");
        $table->dropColumn("number");

        $table = $schema->getTable("cell_aliquote");
        $table->dropColumn("parent_aliquot_id");
        $table->removeForeignKey("FK_E2BD616398D0C6D0");
        $table->dropIndex("IDX_E2BD616398D0C6D0");
    }
}
