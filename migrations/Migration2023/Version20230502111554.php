<?php

declare(strict_types=1);

namespace DoctrineMigrations2023;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230502111554 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Introduces a UserGroup table and ammends some table with a owner field';
    }

    public function up(Schema $schema): void
    {
        // Table "User groups"
        $table = $schema->createTable("user_group");
        $table->addColumn("id", "guid")->setNotnull(true)->setComment('(DC2Type:ulid)');
        $table->addColumn("short_name", "string")->setLength(50)->setNotnull(true);

        $table->setPrimaryKey(["id"]);
        $table->addUniqueIndex(["short_name"], "UNIQ_8F02BF9D3EE4B093");

        // Table "Cell Aliquots"
        $table = $schema->getTable("cell_aliquote");
        $table->addColumn("owner_id", "guid")->setNotnull(false)->setComment('(DC2Type:ulid)');
        $table->addColumn("group_id", "guid")->setNotnull(false)->setComment('(DC2Type:ulid)');
        $table->addColumn("privacy_level", "smallint")->setNotnull(true)->setDefault(2);

        $table->addForeignKeyConstraint("user_accounts", ["owner_id"], ["id"], ["onDelete" => "SET NULL"], "FK_E2BD61637E3C61F9");
        $table->addForeignKeyConstraint("user_group", ["group_id"], ["id"], ["onDelete" => "SET NULL"], "FK_E2BD6163FE54D947");
        $table->addIndex(["owner_id"], "IDX_E2BD61637E3C61F9");
        $table->addIndex(["group_id"], "IDX_E2BD6163FE54D947");

        // Table "Cell"
        $table = $schema->getTable("cell");
        $table->addColumn("owner_id", "guid")->setNotnull(false)->setComment('(DC2Type:ulid)');
        $table->addColumn("group_id", "guid")->setNotnull(false)->setComment('(DC2Type:ulid)');
        $table->addColumn("privacy_level", "smallint")->setNotnull(true)->setDefault(2);

        $table->addForeignKeyConstraint("user_accounts", ["owner_id"], ["id"], ["onDelete" => "SET NULL"], "FK_CB8787E27E3C61F9");
        $table->addForeignKeyConstraint("user_group", ["group_id"], ["id"], ["onDelete" => "SET NULL"], "FK_CB8787E2FE54D947");
        $table->addIndex(["owner_id"], "IDX_CB8787E27E3C61F9");
        $table->addIndex(["group_id"], "IDX_CB8787E2FE54D947");

        // Table "Instrument"
        $table = $schema->getTable("instrument");
        $table->addColumn("owner_id", "guid")->setNotnull(false)->setComment('(DC2Type:ulid)');
        $table->addColumn("group_id", "guid")->setNotnull(false)->setComment('(DC2Type:ulid)');
        $table->getColumn("default_reservation_length")->setNotnull(true)->setDefault(1);
        $table->addColumn("privacy_level", "smallint")->setNotnull(true)->setDefault(2);

        $table->addForeignKeyConstraint("user_accounts", ["owner_id"], ["id"], ["onDelete" => "SET NULL"], "FK_3CBF69DD7E3C61F9");
        $table->addForeignKeyConstraint("user_group", ["group_id"], ["id"], ["onDelete" => "SET NULL"], "FK_3CBF69DDFE54D947");

        $table->addIndex(["owner_id"], "FK_3CBF69DD7E3C61F9");
        $table->addIndex(["group_id"], "IDX_3CBF69DDFE54D947");

        $table->addUniqueIndex(["group_id", "short_name"], "UNIQ_3CBF69DDFE54D9473EE4B093");

        // Table "User accounts"
        $table = $schema->getTable("user_accounts");
        $table->addColumn("group_id", "guid")->setNotnull(false)->setComment('(DC2Type:ulid)');

        $table->addForeignKeyConstraint("user_group", ["group_id"], ["id"], ["onDelete" => "SET NULL"], "FK_2A457AACFE54D947");

        $table->addIndex(["group_id"], "IDX_2A457AACFE54D947");

        // Table experiments: Migration for optional owner
        $table = $schema->getTable("experiment");
        $table->removeForeignKey("FK_136F58B27E3C61F9");
        $table->getColumn("owner_id")->setNotnull(false)->setDefault(null);
        $table->addForeignKeyConstraint("user_accounts", ["owner_id"], ["id"], ["onDelete" => "SET NULL"], "FK_136F58B27E3C61F9");

        $table = $schema->getTable("experimental_run");
        $table->removeForeignKey("FK_30B5493E7E3C61F9");
        $table->getColumn("owner_id")->setNotnull(false)->setDefault(null);
        $table->addForeignKeyConstraint("user_accounts", ["owner_id"], ["id"], ["onDelete" => "SET NULL"], "FK_30B5493E7E3C61F9");
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("cell_aliquote");
        $table->removeForeignKey("FK_E2BD6163FE54D947");
        $table->removeForeignKey("FK_E2BD61637E3C61F9");
        $table->dropIndex("IDX_E2BD61637E3C61F9");
        $table->dropIndex("IDX_E2BD6163FE54D947");
        $table->dropColumn("group_id");
        $table->dropColumn("owner_id");
        $table->dropColumn("privacy_level");

        $table = $schema->getTable("instrument");
        $table->removeForeignKey("FK_3CBF69DD7E3C61F9");
        $table->removeForeignKey("FK_3CBF69DDFE54D947");
        $table->dropIndex("IDX_3CBF69DD7E3C61F9");
        $table->dropIndex("IDX_3CBF69DDFE54D947");
        $table->dropColumn("group_id");
        $table->dropColumn("owner_id");
        $table->dropColumn("privacy_level");
        $table->getColumn("default_reservation_length")->setNotnull(false);

        $table = $schema->getTable("user_accounts");
        $table->removeForeignKey("FK_2A457AACFE54D947");
        $table->dropIndex("IDX_2A457AACFE54D947");
        $table->dropColumn("group_id");

        $schema->dropTable("user_group");

        // Table experiments: Migration for optional owner
        $table = $schema->getTable("experiment");
        $table->removeForeignKey("FK_136F58B27E3C61F9");
        $table->getColumn("owner_id")->setNotnull(true);
        $table->addForeignKeyConstraint("user_accounts", ["owner_id"], ["id"], ["onDelete" => "SET NULL"], "FK_136F58B27E3C61F9");

        $table = $schema->getTable("experimental_run");
        $table->removeForeignKey("FK_30B5493E7E3C61F9");
        $table->getColumn("owner_id")->setNotnull(true);
        $table->addForeignKeyConstraint("user_accounts", ["owner_id"], ["id"], ["onDelete" => "SET NULL"], "FK_30B5493E7E3C61F9");
    }
}
