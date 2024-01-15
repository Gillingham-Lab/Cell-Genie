<?php

declare(strict_types=1);

namespace DoctrineMigrations2024;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20240115063025 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds ownership to rack (location) and box.';
    }

    public function up(Schema $schema): void
    {
        $rackTable = $schema->getTable("rack");
        $rackTable->addColumn("owner_id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");
        $rackTable->addColumn("group_id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");
        $rackTable->addColumn("privacy_level", Types::SMALLINT)->setNotnull(true)->setDefault(2);

        $rackTable->addIndex(["owner_id"], "IDX_3DD796A87E3C61F9");
        $rackTable->addIndex(["group_id"], "IDX_3DD796A8FE54D947");
        $rackTable->addForeignKeyConstraint("user_accounts", ["owner_id"], ["id"], ["onDelete" => "SET NULL"], "FK_3DD796A87E3C61F9");
        $rackTable->addForeignKeyConstraint("user_group", ["group_id"], ["id"], ["onDelete" => "SET NULL"], "FK_3DD796A8FE54D947");

        $boxTable = $schema->getTable("box");
        $boxTable->addColumn("owner_id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");
        $boxTable->addColumn("group_id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");
        $boxTable->addColumn("privacy_level", Types::SMALLINT)->setNotnull(true)->setDefault(2);

        $boxTable->addIndex(["owner_id"], "IDX_8A9483A7E3C61F9");
        $boxTable->addIndex(["group_id"], "IDX_8A9483AFE54D947");
        $boxTable->addForeignKeyConstraint("user_accounts", ["owner_id"], ["id"], ["onDelete" => "SET NULL"], "FK_8A9483A7E3C61F9");
        $boxTable->addForeignKeyConstraint("user_group", ["group_id"], ["id"], ["onDelete" => "SET NULL"], "FK_8A9483AFE54D947");
    }

    public function down(Schema $schema): void
    {
        $rackTable = $schema->getTable("rack");
        $rackTable->dropIndex("IDX_3DD796A87E3C61F9");
        $rackTable->dropIndex("IDX_3DD796A8FE54D947");
        $rackTable->removeForeignKey("FK_3DD796A87E3C61F9");
        $rackTable->removeForeignKey("FK_3DD796A8FE54D947");
        $rackTable->dropColumn("owner_id");
        $rackTable->dropColumn("group_id");
        $rackTable->dropColumn("privacy_level");

        $boxTable = $schema->getTable("box");
        $boxTable->dropIndex("IDX_8A9483A7E3C61F9");
        $boxTable->dropIndex("IDX_8A9483AFE54D947");
        $boxTable->removeForeignKey("FK_8A9483A7E3C61F9");
        $boxTable->removeForeignKey("FK_8A9483AFE54D947");
        $boxTable->dropColumn("owner_id");
        $boxTable->dropColumn("group_id");
        $boxTable->dropColumn("privacy_level");
    }
}
