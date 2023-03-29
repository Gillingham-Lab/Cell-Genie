<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230327061736 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Introduces an instrument table';
    }

    public function up(Schema $schema): void
    {
        // Instrument table
        $table = $schema->createTable("instrument");
        $table->addColumn("id", "ulid")->setNotnull(true)->setComment("(DC2Type:ulid)");
        $table->addColumn("parent_id", "ulid")->setNotnull(false)->setComment("(DC2Type:ulid)");
        $table->addColumn("instrument_number", "string")->setNotnull(true)->setLength(20);

        $table->addColumn("short_name", "string")->setNotnull(true)->setLength(50);
        $table->addColumn("long_name", "string")->setNotnull(true)->setLength(255);

        $table->addColumn("location", "string")->setNotnull(false)->setLength(255);
        $table->addColumn("model_number", "string")->setNotnull(true)->setLength(255);
        $table->addColumn("serial_number", "string")->setNotnull(true)->setLength(255);
        $table->addColumn("registration_number", "string")->setNotnull(false)->setLength(255);
        $table->addColumn("instrument_contact", "string")->setNotnull(false)->setLength(255);
        $table->addColumn("calendar_id", "string")->setNotnull(false)->setLength(255);

        $table->addColumn("description", "text")->setNotnull(false);
        $table->addColumn("auth_string", "text")->setNotnull(false);

        $table->addColumn("requires_training", "boolean")->setNotnull(true)->setDefault(false);
        $table->addColumn("requires_reservation", "boolean")->setNotnull(true)->setDefault(false);
        $table->addColumn("modular", "boolean")->setNotnull(true)->setDefault(false);
        $table->addColumn("collective", "boolean")->setNotnull(true)->setDefault(false);

        $table->addColumn("last_maintenance", "datetime")->setNotnull(false);
        $table->addColumn("acquired_on", "datetime")->setNotnull(false);
        $table->addColumn("default_reservation_length", "float")->setNotnull(false)->setDefault(1);

        $table->setPrimaryKey(["id"]);
        $table->addUniqueIndex(["short_name"], "UNIQ_3CBF69DD3EE4B093");
        $table->addUniqueIndex(["instrument_number"], "UNIQ_3CBF69DDA2261C1C");
        $table->addIndex(["parent_id"], "IDX_3CBF69DD727ACA70");

        $table->addForeignKeyConstraint("instrument", ["parent_id"], ["id"], ["onDelete" => "SET NULL"], "FK_3CBF69DD727ACA70");

        // Association Instrument<=>User
        $table = $schema->createTable("instrument_user");
        $table->addColumn("instrument_id", "ulid")->setNotnull(true)->setComment("(DC2Type:ulid)");
        $table->addColumn("user_id", "ulid")->setNotnull(true)->setComment("(DC2Type:ulid)");
        $table->addColumn("role", "string")->setNotnull(true)->setDefault("untrained");
        $table->setPrimaryKey(["instrument_id", "user_id"]);

        $table->addIndex(["instrument_id"], "IDX_5A0C65E1CF11D9C");
        $table->addIndex(["user_id"], "IDX_5A0C65E1A76ED395");

        // Foreign keys
        $table->addForeignKeyConstraint("instrument", ["instrument_id"], ["id"], ["onDelete" => "CASCADE"], "FK_CC591180CF11D9C");
        $table->addForeignKeyConstraint("user_accounts", ["user_id"], ["id"], ["onDelete" => "CASCADE"], "FK_CC591180A76ED395");

        // Association Instrument<=>File
        $table = $schema->createTable("instrument_file");
        $table->addColumn("instrument_id", "guid")->setNotnull(true)->setComment("(DC2Type:ulid)");
        $table->addColumn("file_id", "guid")->setNotnull(true)->setComment("(DC2Type:ulid)");

        $table->setPrimaryKey(["instrument_id", "file_id"]);
        $table->addIndex(["instrument_id"], "IDX_5B0085B8CF11D9C");
        $table->addUniqueIndex(["file_id"], "UNIQ_5B0085B893CB796C");

        $table->addForeignKeyConstraint("instrument", ["instrument_id"], ["id"], ["onDelete" => "CASCADE"], "FK_5B0085B8CF11D9C");
        $table->addForeignKeyConstraint("file", ["file_id"], ["id"], ["onDelete" => "CASCADE"], "FK_5B0085B893CB796C");
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable("instrument");
        $schema->dropTable("instrument_user");
        $schema->dropTable("instrument_file");
    }
}
