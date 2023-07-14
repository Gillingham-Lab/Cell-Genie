<?php
declare(strict_types=1);

namespace DoctrineMigrations2023;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230714081413 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make Substance and Lot entities privacy aware.';
    }

    public function up(Schema $schema): void
    {
        // Substance table
        $table = $schema->getTable("substance");

        $table->addColumn("owner_id", "guid")
            ->setNotnull(false)
            ->setComment("(DC2Type:ulid)");
        $table->addColumn("group_id", "guid")
            ->setNotnull(false)
            ->setComment("(DC2Type:ulid)");
        $table->addColumn("privacy_level", "smallint")
            ->setNotnull(true)
            ->setDefault(2);

        $table->addIndex(["owner_id"], "IDX_E481CB197E3C61F9");
        $table->addIndex(["group_id"], "IDX_E481CB19FE54D947");

        $table->addForeignKeyConstraint("user_accounts", ["owner_id"], ["id"], ["onDelete" => "SET NULL"], "FK_E481CB197E3C61F9");
        $table->addForeignKeyConstraint("user_group", ["group_id"], ["id"], ["onDelete" => "SET NULL"], "FK_E481CB19FE54D947");

        // Lot table
        $table = $schema->getTable("lot");

        $table->addColumn("owner_id", "guid")
            ->setNotnull(false)
            ->setComment("(DC2Type:ulid)");
        $table->addColumn("group_id", "guid")
            ->setNotnull(false)
            ->setComment("(DC2Type:ulid)");
        $table->addColumn("privacy_level", "smallint")
            ->setNotnull(true)
            ->setDefault(2);

        $table->addIndex(["owner_id"], "IDX_B81291B7E3C61F9");
        $table->addIndex(["group_id"], "IDX_B81291BFE54D947");

        $table->addForeignKeyConstraint("user_accounts", ["owner_id"], ["id"], ["onDelete" => "SET NULL"], "FK_B81291B7E3C61F9");
        $table->addForeignKeyConstraint("user_group", ["group_id"], ["id"], ["onDelete" => "SET NULL"], "FK_B81291BFE54D947");
    }

    public function down(Schema $schema): void
    {
        // Substance table
        $table = $schema->getTable("substance");

        $table->dropColumn("owner_id");
        $table->dropColumn("group_id");
        $table->dropColumn("privacy_level");

        $table->removeForeignKey("FK_E481CB197E3C61F9");
        $table->removeForeignKey("FK_E481CB19FE54D947");

        $table->dropIndex("IDX_E481CB197E3C61F9");
        $table->dropIndex("IDX_E481CB19FE54D947");

        // Lot table
        $table = $schema->getTable("lot");

        $table->dropColumn("owner_id");
        $table->dropColumn("group_id");
        $table->dropColumn("privacy_level");

        $table->removeForeignKey("FK_B81291B7E3C61F9");
        $table->removeForeignKey("FK_B81291BFE54D947");

        $table->dropIndex("IDX_B81291B7E3C61F9");
        $table->dropIndex("IDX_B81291BFE54D947");
    }
}
