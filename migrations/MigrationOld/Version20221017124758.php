<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221017124758 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Database cleanup';
    }

    public function up(Schema $schema): void
    {
        $schema->getTable("antibody")->removeForeignKey("fk_5c97c6b11d6c21c8");

        $schema->getTable("epitope_protein_protein")->removeForeignKey("fk_6ffbd7dc9926e711");
        $schema->getTable("epitope_protein_protein")->removeForeignKey("fk_6ffbd7dce49cde9d");

        $schema->getTable("epitope_host")->removeForeignKey("fk_5c015ac9bf396750");

        $schema->getTable("epitope_small_molecule")->removeForeignKey("fk_e4be7686bf396750");

        $schema->getTable("epitope_protein")->removeForeignKey("fk_482cdddcbf396750");

        $schema->getTable("epitope_small_molecule_chemical")->removeForeignKey("fk_22f62418325508d6");
        $schema->getTable("epitope_small_molecule_chemical")->removeForeignKey("fk_22f62418d645e21d");

        $schema->dropTable("epitope_protein_protein");
        $schema->dropTable("epitope_host");
        $schema->dropTable("epitope_small_molecule");
        $schema->dropTable("epitope_small_molecule_chemical");


        $schema->getTable("antibody")->dropIndex("idx_5c97c6b11d6c21c8");
        $schema->getTable("antibody")->dropColumn("host_organism_id");

        $schema->getTable("cell_file")->removeForeignKey("FK_3F545FB693CB796C");
        $schema->getTable("cell_file")->addForeignKeyConstraint("file", ["file_id"], ["id"], ["onDelete" => "CASCADE"], "FK_3F545FB693CB796C");

        $schema->getTable("cell_aliquote")->getColumn("max_vials")->setDefault(0);

        $schema->getTable("ext_log_entries")->getColumn("id")->setDefault(null);

        $schema->getTable("lot_file")->removeForeignKey("FK_18B3A850A8CBA5F7");

        $schema->getTable("lot_file")->addForeignKeyConstraint("file", ["file_id"], ["id"], ["onDelete" => "CASCADE"], "FK_18B3A85093CB796C");
        $schema->getTable("lot_file")->addForeignKeyConstraint("lot", ["lot_id"], ["id"], ["onDelete" => "CASCADE"], "FK_18B3A850A8CBA5F7");

        $schema->getTable("oligo")
            ->dropColumn("concentration")
            ->dropColumn("amount_ordered")
            ->dropColumn("amount_left")
            ->dropColumn("purification")
        ;

        $t = $schema->getTable("substance_file");
        $t->removeForeignKey("fk_c7e39da0232d562b");
        $t->removeForeignKey("FK_C7E39DA093CB796C");
        $t->addForeignKeyConstraint("substance", ["substance_ulid"], ["ulid"], [], "FK_C7E39DA089F463E9");
        $t->addForeignKeyConstraint("file", ["file_id"], ["id"], ["onDelete" => "CASCADE"], "FK_C7E39DA093CB796C");

        $t->renameIndex("idx_c7e39da0232d562b", "IDX_C7E39DA089F463E9");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
