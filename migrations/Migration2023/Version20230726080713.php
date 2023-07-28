<?php
declare(strict_types=1);

namespace DoctrineMigrations2023;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20230726080713 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a log table and makes Instruments having log entries.';
    }

    public function up(Schema $schema): void
    {
        // Log table
        $table = $schema->createTable("log");
        $table->addColumn("id", Types::GUID)->setNotnull(true)->setComment("(DC2Type:ulid)");
        $table->addColumn("owner_id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");
        $table->addColumn("group_id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");
        $table->addColumn("log_type", Types::STRING)->setNotnull(true)->setLength(255);
        $table->addColumn("title", Types::STRING)->setNotnull(true)->setLength(255);
        $table->addColumn("description", Types::TEXT)->setNotnull(false);
        $table->addColumn("privacy_level", Types::SMALLINT)->setNotnull(true)->setDefault(2);
        $table->addColumn("created_at", Types::DATETIME_MUTABLE)->setNotnull(false);
        $table->addColumn("modified_at", Types::DATETIME_MUTABLE)->setNotnull(false);

        $table->setPrimaryKey(["id"]);
        $table->addIndex(["owner_id"], "IDX_8F3F68C57E3C61F9");
        $table->addIndex(["group_id"], "IDX_8F3F68C5FE54D947");

        $table->addForeignKeyConstraint("user_accounts", ["owner_id"], ["id"], ["onDelete" => "SET NULL"], "FK_8F3F68C57E3C61F9");
        $table->addForeignKeyConstraint("user_group", ["group_id"], ["id"], ["onDelete" => "SET NULL"], "FK_8F3F68C5FE54D947");

        // instrument log join table
        $table = $schema->createTable("instrument_log");
        $table->addColumn("instrument_id", Types::GUID)->setNotnull(true)->setComment("(DC2Type:ulid)");
        $table->addColumn("log_id", Types::GUID)->setNotnull(true)->setComment("(DC2Type:ulid)");

        $table->setPrimaryKey(["instrument_id", "log_id"]);
        $table->addIndex(["instrument_id"], "IDX_1D5E2E24CF11D9C");
        $table->addUniqueIndex(["log_id"], "UNIQ_1D5E2E24EA675D86");

        $table->addForeignKeyConstraint("instrument", ["instrument_id"], ["id"], ["onDelete" => "CASCADE"], "FK_1D5E2E24CF11D9C");
        $table->addForeignKeyConstraint("log", ["log_id"], ["id"], ["onDelete" => "CASCADE"], "FK_1D5E2E24EA675D86");
    }

    public function down(Schema $schema): void
    {
        // Remove instrument log join table
        $table = $schema->getTable("instrument_log");
        $table->removeForeignKey("FK_1D5E2E24CF11D9C");
        $table->removeForeignKey("FK_1D5E2E24EA675D86");

        $schema->dropTable("instrument_log");

        // Remove log table
        $table = $schema->getTable("log");
        $table->removeForeignKey("FK_8F3F68C57E3C61F9");
        $table->removeForeignKey("FK_8F3F68C5FE54D947");

        $schema->dropTable("log");
    }
}
