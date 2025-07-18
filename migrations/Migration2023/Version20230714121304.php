<?php
declare(strict_types=1);

namespace DoctrineMigrations2023;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230714121304 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Makes cell cultures and events privacy aware.';
    }

    public function up(Schema $schema): void
    {
        // Cell culture table
        $table = $schema->getTable("cell_culture");

        $table->addColumn("group_id", "guid")
            ->setNotnull(false)
            ->setComment("(DC2Type:ulid)");
        $table->addColumn("privacy_level", "smallint")
            ->setNotnull(true)
            ->setDefault(2);

        $table->addIndex(["group_id"], "IDX_D3D5765CFE54D947");

        $table->addForeignKeyConstraint("user_group", ["group_id"], ["id"], ["onDelete" => "SET NULL"], "FK_D3D5765CFE54D947");

        // Cell culture event table
        $table = $schema->getTable("cell_culture_event");

        $table->addColumn("group_id", "guid")
            ->setNotnull(false)
            ->setComment("(DC2Type:ulid)");
        $table->addColumn("privacy_level", "smallint")
            ->setNotnull(true)
            ->setDefault(2);

        $table->addIndex(["group_id"], "IDX_220DF91AFE54D947");

        $table->addForeignKeyConstraint("user_group", ["group_id"], ["id"], ["onDelete" => "SET NULL"], "FK_220DF91AFE54D947");
    }

    public function down(Schema $schema): void
    {
        // Cell culture table
        $table = $schema->getTable("cell_culture");

        $table->dropColumn("group_id");
        $table->dropColumn("privacy_level");

        $table->removeForeignKey("FK_D3D5765CFE54D947");

        $table->dropIndex("IDX_D3D5765CFE54D947");

        // Cell culture event table
        $table = $schema->getTable("cell_culture_event");

        $table->dropColumn("group_id");
        $table->dropColumn("privacy_level");

        $table->removeForeignKey("FK_220DF91AFE54D947");

        $table->dropIndex("IDX_220DF91AFE54D947");
    }
}
