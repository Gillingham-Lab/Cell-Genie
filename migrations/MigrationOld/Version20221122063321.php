<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221122063321 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Adds a relation from cell to plasmid and makes lots to be 'nullable'";
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE box_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE rack_id_seq CASCADE');

        $table = $schema->getTable("cell");
        $table->dropColumn("engineering_plasmid");
        $table->addColumn("engineering_plasmid_id", "guid")->setNotnull(false)->setComment("(DC2Type:ulid)");
        $table->addForeignKeyConstraint("plasmid", ["engineering_plasmid_id"], ["ulid"], ["onDelete" => "SET NULL"], "FK_CB8787E21FB67AB2");
        $table->addIndex(["engineering_plasmid_id"], "IDX_CB8787E21FB67AB2");

        $table = $schema->getTable("lot");
        $table->getColumn("opened_on")->setNotnull(false);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
