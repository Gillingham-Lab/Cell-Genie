<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221021064335 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates a new plasmid substance';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable("plasmid");

        $table->addColumn("ulid", "guid")->setNotnull(true)->setComment("(DC2Type:ulid)");
        $table->addColumn("number", "string")
            ->setNotnull(false)
            ->setLength(10)
            ->setDefault("???");
        $table->addColumn("expression_in_id", "integer")->setNotnull(false);
        $table->addColumn("created_by_id", "guid")->setNotnull(false)->setComment("(DC2Type:ulid)");
        $table->addColumn("plasmid_map_id", "guid")->setNotnull(false)->setComment("(DC2Type:ulid)");

        $table->addColumn("growth_resistance", "string")->setLength(255)->setNotnull(true);
        $table->addColumn("expression_resistance", "string")->setLength(255)->setNotnull(true);

        $table->addColumn("for_production", "boolean")->setNotnull(true);
        $table->addColumn("comment", "text")->setNotnull(false);
        $table->addColumn("labjournal", "text")->setNotnull(false);
        $table->addColumn("sequence", "text")->setNotnull(false);
        $table->addColumn("sequence_length", "integer")->setNotnull(false);
        $table->addColumn("rrid", "string")->setLength(255)->setNotnull(false);

        $table->setPrimaryKey(["ulid"]);
        $table->addIndex(["expression_in_id"], "IDX_6D05BC27957FE748");
        $table->addIndex(["created_by_id"], "IDX_6D05BC27B03A8386");
        $table->addIndex(["plasmid_map_id"], "IDX_6D05BC27CC48CC55");

        $table->addForeignKeyConstraint("organism", ["expression_in_id"], ["id"], ["onDelete" => "SET NULL"], "FK_6D05BC27957FE748");
        $table->addForeignKeyConstraint("user_accounts", ["created_by_id"], ["id"], ["onDelete" => "SET NULL"], "FK_6D05BC27B03A8386");
        $table->addForeignKeyConstraint("file", ["plasmid_map_id"], ["id"], ["onDelete" => "SET NULL"], "FK_6D05BC27CC48CC55");
        $table->addForeignKeyConstraint("substance", ["ulid"], ["ulid"], ["onDelete" => "CASCADE"], "FK_6D05BC27C288C859");

        $table2 = $schema->createTable("plasmid_expressed_proteins");
        $table2->addColumn("plasmid_ulid", "guid")->setNotnull(true)->setComment("(DC2Type:ulid)");
        $table2->addColumn("protein_ulid", "guid")->setNotnull(true)->setComment("(DC2Type:ulid)");
        $table2->setPrimaryKey(["plasmid_ulid", "protein_ulid"]);

        $table2->addIndex(["plasmid_ulid"], "IDX_DB755AD6D706D75");
        $table2->addIndex(["protein_ulid"], "IDX_DB755AD9926E711");

        $table2->addForeignKeyConstraint("plasmid", ["plasmid_ulid"], ["ulid"], ["onDelete" => "CASCADE"], "FK_DB755AD6D706D75");
        $table2->addForeignKeyConstraint("protein", ["protein_ulid"], ["ulid"], ["onDelete" => "CASCADE"], "FK_DB755AD9926E711");
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("plasmid");
        $table->removeForeignKey("FK_6D05BC27957FE748");
        $table->removeForeignKey("FK_6D05BC27B03A8386");
        $table->removeForeignKey("FK_6D05BC27CC48CC55");
        $table->removeForeignKey("FK_6D05BC27C288C859");

        $table2 = $schema->getTable("plasmid_expressed_proteins");
        $table2->removeForeignKey("FK_DB755AD6D706D75");
        $table2->removeForeignKey("FK_DB755AD9926E711");

        $schema->dropTable("plasmid");
        $schema->dropTable("plasmid_expressed_proteins");
    }
}
