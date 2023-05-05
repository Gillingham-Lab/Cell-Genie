<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Exception\IrreversibleMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211005092007 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // $this->addSql('DROP SEQUENCE antibody_dilution_id_seq CASCADE');
        $schema->dropSequence("antibody_dilution_id_seq");

        // $this->addSql('ALTER TABLE antibody_dilution ALTER experiment_id DROP NOT NULL');
        $schema->getTable("antibody_dilution")->getColumn("experiment_id")->setNotnull(false);

        //$this->addSql('ALTER TABLE experiment DROP CONSTRAINT FK_136F58B2EB0F4B39');
        $experiment = $schema->getTable("experiment");
        $experiment->removeForeignKey("FK_136F58B2EB0F4B39");
        //$this->addSql('ALTER TABLE experiment ADD CONSTRAINT FK_136F58B2C14C34C2 FOREIGN KEY (wellplate_id) REFERENCES culture_flask (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $experiment->addForeignKeyConstraint(
            foreignTable: "culture_flask",
            localColumnNames: ["wellplate_id"],
            foreignColumnNames: ["id"],
            options: ["onDelete" => "SET NULL"],
            name: "FK_136F58B2C14C34C2"
        );
        //$this->addSql('ALTER TABLE experiment ADD CONSTRAINT FK_136F58B2EB0F4B39 FOREIGN KEY (experiment_type_id) REFERENCES experiment_type (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $experiment->addForeignKeyConstraint(
            foreignTable: "experiment_type",
            localColumnNames: ["experiment_type_id"],
            foreignColumnNames: ["id"],
            options: ["onDelete" => "CASCADE"],
            name: "FK_136F58B2EB0F4B39"
        );

        //$this->addSql('ALTER TABLE experiment_type ALTER parent_id DROP NOT NULL');
        $schema->getTable("experiment_type")->getColumn("parent_id")->setNotnull(false);

        $experimentalCondition = $schema->getTable("experimental_condition");
        // $this->addSql('DROP INDEX UNIQ_6E25798CFF444C82B36786B');
        $experimentalCondition->dropIndex("UNIQ_6E25798CFF444C82B36786B");
        // $this->addSql('ALTER TABLE experimental_condition ALTER general SET NOT NULL');
        $experimentalCondition->getColumn("general")->setNotnull(true);
        // $this->addSql('ALTER TABLE experimental_condition ALTER "order" SET NOT NULL');
        $experimentalCondition->getColumn("order")->setNotnull(true);
        //$this->addSql('CREATE UNIQUE INDEX UNIQ_6E25798CFF444C82B36786B ON experimental_condition (experiment_id, title)');
        $experimentalCondition->addUniqueIndex(["experiment_id", "title"], "UNIQ_6E25798CFF444C82B36786B");

        $experimentalMeasurement = $schema->getTable("experimental_measurement");
        // $this->addSql('DROP INDEX UNIQ_AA3802E9FF444C82B36786B');
        $experimentalMeasurement->dropIndex("UNIQ_AA3802E9FF444C82B36786B");
        //$this->addSql('ALTER TABLE experimental_measurement ALTER "order" SET NOT NULL');
        $experimentalMeasurement->getColumn("order")->setNotnull(true);
        //$this->addSql('CREATE UNIQUE INDEX UNIQ_AA3802E9FF444C82B36786B ON experimental_measurement (experiment_id, title)');
        $experimentalMeasurement->addUniqueIndex(["experiment_id", "title"], "UNIQ_AA3802E9FF444C82B36786B");
    }

    public function down(Schema $schema): void
    {
        throw new IrreversibleMigration();
    }
}
