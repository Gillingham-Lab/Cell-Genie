<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220729052919 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable("epitope");
        $table->addColumn("id", "guid")->setComment("(DC2Type:ulid)");
        $table->addColumn("short_name", "string")->setLength(20)->setNotnull(true);
        $table->addColumn("epitope_type", "string")->setLength(20)->setNotnull(true);
        $table->setPrimaryKey(["id"]);

        $table = $schema->createTable("epitope_host");
        $table->addColumn("id", "guid")->setComment("(DC2Type:ulid)");
        $table->setPrimaryKey(["id"]);
        $table->addForeignKeyConstraint("epitope", ["id"], ["id"], ["onDelete" => "cascade"], "FK_5C015AC9BF396750");

        $table = $schema->createTable("epitope_host_antibody_host");
        $table->addColumn("epitope_host_id", "guid")->setComment("(DC2Type:ulid)");
        $table->addColumn("antibody_host_id", "guid")->setComment("(DC2Type:ulid)");
        $table->setPrimaryKey(["epitope_host_id", "antibody_host_id"]);
        $table->addIndex(["epitope_host_id"], "IDX_1BC9BA13CF6CEDEB");
        $table->addIndex(["antibody_host_id"], "IDX_1BC9BA1323E14841");
        $table->addForeignKeyConstraint("epitope_host", ["epitope_host_id"], ["id"], ["onDelete" => "cascade"], "FK_1BC9BA13CF6CEDEB");
        $table->addForeignKeyConstraint("antibody_host", ["antibody_host_id"], ["id"], ["onDelete" => "cascade"], "FK_1BC9BA1323E14841");

        $table = $schema->createTable("epitope_protein");
        $table->addColumn("id", "guid")->setComment("(DC2Type:ulid)");
        $table->setPrimaryKey(["id"]);
        $table->addForeignKeyConstraint("epitope", ["id"], ["id"], ["onDelete" => "cascade"], "FK_482CDDDCBF396750");

        $table = $schema->createTable("epitope_protein_protein");
        $table->addColumn("epitope_protein_id", "guid")->setComment("(DC2Type:ulid)");
        $table->addColumn("protein_ulid", "guid")->setComment("(DC2Type:ulid)");
        $table->setPrimaryKey(["epitope_protein_id", "protein_ulid"]);
        $table->addIndex(["epitope_protein_id"], "IDX_6FFBD7DCE49CDE9D");
        $table->addIndex(["protein_ulid"], "IDX_6FFBD7DC9926E711");
        $table->addForeignKeyConstraint("epitope_protein", ["epitope_protein_id"], ["id"], ["onDelete" => "cascade"], "FK_6FFBD7DCE49CDE9D");
        $table->addForeignKeyConstraint("protein", ["protein_ulid"], ["ulid"], ["onDelete" => "cascade"], "FK_6FFBD7DC9926E711");

        $table = $schema->createTable("epitope_small_molecule");
        $table->addColumn("id", "guid")->setComment("(DC2Type:ulid)");
        $table->setPrimaryKey(["id"]);
        $table->addForeignKeyConstraint("epitope", ["id"], ["id"], ["onDelete" => "cascade"], "FK_E4BE7686BF396750");

        $table = $schema->createTable("epitope_small_molecule_chemical");
        $table->addColumn("epitope_small_molecule_id", "guid")->setComment("(DC2Type:ulid)");
        $table->addColumn("chemical_ulid", "guid")->setComment("(DC2Type:ulid)");
        $table->setPrimaryKey(["epitope_small_molecule_id", "chemical_ulid"]);
        $table->addIndex(["epitope_small_molecule_id"], "IDX_22F62418D645E21D");
        $table->addIndex(["chemical_ulid"], "IDX_22F62418325508D6");
        $table->addForeignKeyConstraint("epitope_small_molecule", ["epitope_small_molecule_id"], ["id"], ["onDelete" => "cascade"], "FK_22F62418D645E21D");
        $table->addForeignKeyConstraint("chemical", ["chemical_ulid"], ["ulid"], ["onDelete" => "cascade"], "FK_22F62418325508D6");

        $table = $schema->createTable("protein_protein");
        $table->addColumn("protein_parent_ulid", "guid")->setComment("(DC2Type:ulid)");
        $table->addColumn("protein_child_ulid", "guid")->setComment("(DC2Type:ulid)");
        $table->addIndex(["protein_parent_ulid"], "IDX_C5038B13AD6EDB44");
        $table->addIndex(["protein_child_ulid"], "IDX_C5038B131A7EF418");
        $table->addForeignKeyConstraint("protein", ["protein_parent_ulid"], ["ulid"], ["onDelete" => "set null"], "FK_C5038B13AD6EDB44");
        $table->addForeignKeyConstraint("protein", ["protein_child_ulid"], ["ulid"], ["onDelete" => "set null"], "FK_C5038B131A7EF418");
    }

    public function down(Schema $schema): void
    {
        $schema->getTable("epitope_host")->removeForeignKey("FK_5C015AC9BF396750");
        $schema->getTable("epitope_protein")->removeForeignKey("FK_482CDDDCBF396750");
        $schema->getTable("epitope_small_molecule")->removeForeignKey("FK_E4BE7686BF396750");
        $schema->getTable("epitope_host_antibody_host")->removeForeignKey("FK_1BC9BA13CF6CEDEB");
        $schema->getTable("epitope_protein_protein")->removeForeignKey("FK_6FFBD7DCE49CDE9D");
        $schema->getTable("epitope_small_molecule_chemical")->removeForeignKey("FK_22F62418D645E21D");

        $schema->dropTable("epitope");
        $schema->dropTable("epitope_host");
        $schema->dropTable("epitope_host_antibody_host");
        $schema->dropTable("epitope_protein");
        $schema->dropTable("epitope_protein_protein");
        $schema->dropTable("epitope_small_molecule");
        $schema->dropTable("epitope_small_molecule_chemical");
        $schema->dropTable("protein_protein");
    }
}
