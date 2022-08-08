<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220805135431 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Table CellCulture
        $table = $schema->createTable("cell_culture");
        $table->addColumn("id", "guid")
            ->setNotnull(true)
            ->setComment("(DC2Type:ulid)");
        $table->addColumn("owner_id", "guid")
            ->setNotnull(false)
            ->setComment("(DC2Type:ulid)");
        $table->addColumn("aliquot_id", "integer")
            ->setNotnull(false);
        $table->addColumn("parent_cell_culture_id", "guid")
            ->setNotnull(false)
            ->setComment("(DC2Type:ulid)");

        $table->addColumn("unfrozen_on", "date")->setNotnull(true);
        $table->addColumn("trashed_on", "date")->setNotnull(false);
        $table->addColumn("incubator", "string")->setLength(255)->setNotnull(true);
        $table->addColumn("flask", "string")->setLength(255)->setNotnull(true);

        $table->setPrimaryKey(["id"]);
        $table->addIndex(["owner_id"], indexName: "IDX_D3D5765C7E3C61F9");
        $table->addIndex(["aliquot_id"], indexName: "IDX_D3D5765CDF934280");
        $table->addIndex(["parent_cell_culture_id"], indexName: "IDX_D3D5765C1BF7EEE0");

        $table->addForeignKeyConstraint("user_accounts", ["owner_id"], ["id"], ["onDelete" => "SET NULL"], "FK_D3D5765C7E3C61F9");
        $table->addForeignKeyConstraint("cell_aliquote", ["aliquot_id"], ["id"], ["onDelete" => "CASCADE"], "FK_D3D5765CDF934280");
        $table->addForeignKeyConstraint("cell_culture", ["parent_cell_culture_id"], ["id"], ["onDelete" => "CASCADE"], "FK_D3D5765C1BF7EEE0");

        // Table CellCultureEvent
        $table = $schema->createTable("cell_culture_event");
        $table->addColumn("id", "guid")
            ->setNotnull(true)
            ->setComment("(DC2Type:ulid)");
        $table->addColumn("owner_id", "guid")
            ->setNotnull(false)
            ->setComment("(DC2Type:ulid)");
        $table->addColumn("cell_culture_id", "guid")
            ->setNotnull(true)
            ->setComment("(DC2Type:ulid)");
        $table->addColumn("date", "date")->setNotnull(true);
        $table->addColumn("description", "text")->setLength(255)->setNotnull(false);
        $table->addColumn("event_type", "string")->setLength(255)->setNotnull(true);
        $table->addColumn("short_name", "string")->setLength(20)->setNotnull(true);

        $table->setPrimaryKey(["id"]);
        $table->addIndex(["owner_id"], "IDX_220DF91A7E3C61F9");
        $table->addForeignKeyConstraint("user_accounts", ["owner_id"], ["id"], ["onDelete" => "SET NULL"], "FK_220DF91A7E3C61F9");
        $table->addForeignKeyConstraint("cell_culture", ["cell_culture_id"], ["id"], ["onDelete" => "CASCADE"], "FK_220DF91A8C3BEA1C");

        // Create sub tables

        // Other events
        $table = $schema->createTable("cell_culture_other_event");
        $table->addColumn("id", "guid")
            ->setNotnull(true)
            ->setComment("(DC2Type:ulid)");
        $table->setPrimaryKey(["id"]);
        $table->addForeignKeyConstraint("cell_culture_event", ["id"], ["id"], ["onDelete" => "CASCADE"], "FK_B6F3F93BBF396750");

        // Splitting
        $table = $schema->createTable("cell_culture_splitting_event");
        $table->addColumn("id", "guid")
            ->setNotnull(true)
            ->setComment("(DC2Type:ulid)");
        $table->setPrimaryKey(["id"]);
        $table->addForeignKeyConstraint("cell_culture_event", ["id"], ["id"], ["onDelete" => "CASCADE"], "FK_C36547F9BF396750");

        $table->addColumn("splitting", "string")->setLength(255)->setNotnull(true);
        $table->addColumn("new_flask", "string")->setLength(255)->setNotnull(true);

        // Testing
        $table = $schema->createTable("cell_culture_test_event");
        $table->addColumn("id", "guid")
            ->setNotnull(true)
            ->setComment("(DC2Type:ulid)");
        $table->setPrimaryKey(["id"]);
        $table->addForeignKeyConstraint("cell_culture_event", ["id"], ["id"], ["onDelete" => "CASCADE"], "FK_CCA9361BF396750");

        $table->addColumn("result", "string")->setLength(30)->setNotnull(true);
        $table->addColumn("test_type", "string")->setLength(255)->setNotnull(true);
        $table->addColumn("supernatant_amount", "float")->setNotnull(true);
    }

    public function down(Schema $schema): void
    {
        $schema
            ->dropTable("cell_culture")
            ->dropTable("cell_culture_event")
            ->dropTable("cell_culture_other_event")
            ->dropTable("cell_culture_splitting_event")
            ->dropTable("cell_culture_test_event")
        ;
    }
}
