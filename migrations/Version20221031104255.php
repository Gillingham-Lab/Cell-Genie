<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221031104255 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds parent column to plasmids';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("plasmid");
        $table->addColumn("parent_id", "guid")->setNotnull(false)->setComment("(DC2Type:ulid)");

        $table->addForeignKeyConstraint("plasmid", ["parent_id"], ["ulid"], options: ["onDelete" => "SET NULL"], name: "FK_6D05BC27727ACA70");
        $table->addIndex(["parent_id"], "IDX_6D05BC27727ACA70");
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("plasmid");
        $table->dropColumn("parent_id");
        $table->removeForeignKey("FK_6D05BC27727ACA70");
        $table->dropIndex("IDX_6D05BC27727ACA70");
    }
}
