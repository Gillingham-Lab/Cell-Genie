<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Exception\IrreversibleMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211005082724 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        /*
        $this->addSql('CREATE TABLE antibody_dilution (
            id INT NOT NULL,
            antibody_id INT NOT NULL,
            experiment_id UUID DEFAULT NULL,
            dilution VARCHAR(15) NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_16A87BE351162764 ON antibody_dilution (antibody_id)');
        $this->addSql('CREATE INDEX IDX_16A87BE3FF444C8 ON antibody_dilution (experiment_id)');
        $this->addSql('COMMENT ON COLUMN antibody_dilution.experiment_id IS \'(DC2Type:ulid)\'');
        */
        $table = $schema->createTable("antibody_dilution");
        $table->addColumn("id", "guid")
            ->setNotnull(true)
            ->setComment("(DC2Type:ulid)")
        ;
        $table->addColumn("antibody_id", "integer")
            ->setNotnull(true)
        ;
        $table->addColumn("experiment_id", "guid")
            ->setNotnull(true)
            ->setComment("(DC2Type:ulid)")
        ;
        $table->addColumn("dilution", "string")
            ->setLength(15)
            ->setNotnull(true)
        ;

        $table->setPrimaryKey(["id"]);
        $table->addIndex(["antibody_id"], indexName: "IDX_16A87BE351162764");
        $table->addIndex(["experiment_id"], indexName: "IDX_16A87BE3FF444C8");

        /*
        $this->addSql('CREATE TABLE experiment (
            id UUID NOT NULL,
            owner_id UUID NOT NULL,
            experiment_type_id UUID NOT NULL,
            wellplate_id INT DEFAULT NULL,
            name VARCHAR(255) NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            modified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_136F58B27E3C61F9 ON experiment (owner_id)');
        $this->addSql('CREATE INDEX IDX_136F58B2EB0F4B39 ON experiment (experiment_type_id)');
        $this->addSql('CREATE INDEX IDX_136F58B2C14C34C2 ON experiment (wellplate_id)');
        $this->addSql('COMMENT ON COLUMN experiment.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN experiment.owner_id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN experiment.experiment_type_id IS \'(DC2Type:ulid)\'');
        */
        $table = $schema->createTable("experiment");
        $table->addColumn("id", "guid")
            ->setNotnull(true)
            ->setComment("(DC2Type:ulid)")
        ;
        $table->addColumn("owner_id", "guid")
            ->setNotnull(true)
            ->setComment("(DC2Type:ulid)")
        ;
        $table->addColumn("experiment_type_id", "guid")
            ->setNotnull(true)
            ->setComment("(DC2Type:ulid)")
        ;
        $table->addColumn("wellplate_id", "integer")->setNotnull(false);
        $table->addColumn("name", "string")->setLength(255)->setNotnull(true);
        $table->addColumn("created_at", "datetime")->setNotnull(false);
        $table->addColumn("modified_at", "datetime")->setNotnull(false);

        $table->setPrimaryKey(["id"]);
        $table->addIndex(["owner_id"], indexName: "IDX_136F58B27E3C61F9");
        $table->addIndex(["experiment_type_id"], indexName: "IDX_136F58B2EB0F4B39");
        $table->addIndex(["wellplate_id"], indexName: "IDX_136F58B2C14C34C2");

        /*
        $this->addSql('CREATE TABLE experiment_protein (experiment_id UUID NOT NULL, protein_id INT NOT NULL, PRIMARY KEY(experiment_id, protein_id))');
        $this->addSql('CREATE INDEX IDX_B6BB2618FF444C8 ON experiment_protein (experiment_id)');
        $this->addSql('CREATE INDEX IDX_B6BB261854985755 ON experiment_protein (protein_id)');
        $this->addSql('COMMENT ON COLUMN experiment_protein.experiment_id IS \'(DC2Type:ulid)\'');
        */
        $table = $schema->createTable("experiment_protein");
        $table->addColumn("experiment_id", "guid")->setNotnull(true)->setComment("(DC2Type:ulid)");
        $table->addColumn("protein_id", "integer")->setNotnull(true);
        $table->setPrimaryKey(["experiment_id", "protein_id"]);
        $table->addIndex(["experiment_id"], indexName: "IDX_B6BB2618FF444C8");
        $table->addIndex(["protein_id"], indexName: "IDX_B6BB261854985755");

        /*
        $this->addSql('CREATE TABLE experiment_chemical (experiment_id UUID NOT NULL, chemical_id INT NOT NULL, PRIMARY KEY(experiment_id, chemical_id))');
        $this->addSql('CREATE INDEX IDX_B8F4E4F2FF444C8 ON experiment_chemical (experiment_id)');
        $this->addSql('CREATE INDEX IDX_B8F4E4F2E1770A76 ON experiment_chemical (chemical_id)');
        $this->addSql('COMMENT ON COLUMN experiment_chemical.experiment_id IS \'(DC2Type:ulid)\'');
        */
        $table = $schema->createTable("experiment_chemical");
        $table->addColumn("experiment_id", "guid")->setNotnull(true)->setComment("(DC2Type:ulid)");
        $table->addColumn("chemical_id", "integer")->setNotnull(true);
        $table->setPrimaryKey(["experiment_id", "chemical_id"]);
        $table->addIndex(["experiment_id"], indexName: "IDX_B8F4E4F2FF444C8");
        $table->addIndex(["chemical_id"], indexName: "IDX_B8F4E4F2E1770A76");

        /*
        $this->addSql('CREATE TABLE experiment_cell (experiment_id UUID NOT NULL, cell_id INT NOT NULL, PRIMARY KEY(experiment_id, cell_id))');
        $this->addSql('CREATE INDEX IDX_D078464FFF444C8 ON experiment_cell (experiment_id)');
        $this->addSql('CREATE INDEX IDX_D078464FCB39D93A ON experiment_cell (cell_id)');
        $this->addSql('COMMENT ON COLUMN experiment_cell.experiment_id IS \'(DC2Type:ulid)\'');
        */
        $table = $schema->createTable("experiment_cell");
        $table->addColumn("experiment_id", "guid")->setNotnull(true)->setComment("(DC2Type:ulid)");
        $table->addColumn("cell_id", "integer")->setNotnull(true);
        $table->setPrimaryKey(["experiment_id", "cell_id"]);
        $table->addIndex(["experiment_id"], indexName: "IDX_D078464FFF444C8");
        $table->addIndex(["cell_id"], indexName: "IDX_D078464FCB39D93A");

        /*
        $this->addSql('CREATE TABLE experiment_type (
            id UUID NOT NULL,
            created_by_id UUID NOT NULL,
            parent_id UUID DEFAULT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            modified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_97219684B03A8386 ON experiment_type (created_by_id)');
        $this->addSql('CREATE INDEX IDX_97219684727ACA70 ON experiment_type (parent_id)');
        $this->addSql('COMMENT ON COLUMN experiment_type.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN experiment_type.created_by_id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN experiment_type.parent_id IS \'(DC2Type:ulid)\'');
        */
        $table = $schema->createTable("experiment_type");
        $table->addColumn("id", "guid")
            ->setNotnull(true)
            ->setComment("(DC2Type:ulid)")
        ;
        $table->addColumn("created_by_id", "guid")
            ->setNotnull(true)
            ->setComment("(DC2Type:ulid)")
        ;
        $table->addColumn("parent_id", "guid")
            ->setNotnull(true)
            ->setComment("(DC2Type:ulid)")
        ;
        $table->addColumn("name", "string")->setNotnull(true)->setLength(255);
        $table->addColumn("description", "text")->setNotnull(false)->setLength(255);
        $table->addColumn("created_at", "datetime")->setNotnull(false);
        $table->addColumn("modified_at", "datetime")->setNotnull(false);

        $table->setPrimaryKey(["id"]);
        $table->addIndex(["created_by_id"], indexName: "IDX_97219684B03A8386");
        $table->addIndex(["parent_id"], indexName: "IDX_97219684727ACA70");

        /*
        $this->addSql('
            CREATE TABLE experimental_condition (
                id UUID NOT NULL,
                experiment_id UUID DEFAULT NULL,
                general BOOLEAN DEFAULT \'false\' NOT NULL,
                "order" INT DEFAULT 0 NOT NULL,
                title VARCHAR(100) NOT NULL,
                description TEXT DEFAULT NULL,
                PRIMARY KEY(id)
            )
        ');
        $this->addSql('CREATE INDEX IDX_6E25798CFF444C8 ON experimental_condition (experiment_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6E25798CFF444C82B36786B ON experimental_condition (experiment_id, title)');
        $this->addSql('COMMENT ON COLUMN experimental_condition.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN experimental_condition.experiment_id IS \'(DC2Type:ulid)\'');
        */
        $table = $schema->createTable("experimental_condition");
        $table->addColumn("id", typeName: "guid")->setNotnull(true)->setComment("(DC2Type:ulid)");
        $table->addColumn("experiment_id", typeName: "guid")->setNotnull(false)->setComment("(DC2Type:ulid)");
        $table->addColumn("general", typeName: "boolean")->setNotnull(false)->setDefault(false);
        $table->addColumn("order", typeName: "integer")->setNotnull(false)->setDefault(0);
        $table->addColumn("title", typeName: "string")->setLength(100)->setNotnull(true);
        $table->addColumn("description", typeName: "text")->setNotnull(false);

        $table->setPrimaryKey(["id"]);
        $table->addIndex(["experiment_id"], indexName: "IDX_6E25798CFF444C8");
        $table->addIndex(["experiment_id", "title"], indexName: "UNIQ_6E25798CFF444C82B36786B");

        /*
        $this->addSql('CREATE TABLE experimental_measurement (
            id UUID NOT NULL,
            experiment_id INT DEFAULT NULL,
            "order" INT DEFAULT 0 NOT NULL,
            title VARCHAR(100) NOT NULL,
            description TEXT DEFAULT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_AA3802E9FF444C8 ON experimental_measurement (experiment_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AA3802E9FF444C82B36786B ON experimental_measurement (experiment_id, title)');
        $this->addSql('COMMENT ON COLUMN experimental_measurement.id IS \'(DC2Type:ulid)\'');
        */
        $table = $schema->createTable("experimental_measurement");
        $table->addColumn("id", typeName: "guid")->setNotnull(true)->setComment("(DC2Type:ulid)");
        $table->addColumn("experiment_id", typeName: "guid")->setNotnull(false)->setComment("(DC2Type:ulid)");
        $table->addColumn("order", typeName: "integer")->setNotnull(false)->setDefault(0);
        $table->addColumn("title", typeName: "string")->setLength(100)->setNotnull(true);
        $table->addColumn("description", typeName: "text")->setNotnull(false);

        $table->setPrimaryKey(["id"]);
        $table->addIndex(["experiment_id"], indexName: "IDX_AA3802E9FF444C8");
        $table->addIndex(["experiment_id", "title"], indexName: "UNIQ_AA3802E9FF444C82B36786B");

        //$this->addSql('ALTER TABLE antibody_dilution ADD CONSTRAINT FK_16A87BE351162764 FOREIGN KEY (antibody_id) REFERENCES antibody (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $schema->getTable("antibody_dilution")
            ->addForeignKeyConstraint(
                foreignTable: "antibody",
                localColumnNames: ["antibody_id"],
                foreignColumnNames: ["id"],
                options: ["onDelete" => "CASCADE"],
                constraintName: "FK_16A87BE351162764"
            )
        ;

        //$this->addSql('ALTER TABLE antibody_dilution ADD CONSTRAINT FK_16A87BE3FF444C8 FOREIGN KEY (experiment_id) REFERENCES experiment (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $schema->getTable("antibody_dilution")
            ->addForeignKeyConstraint(
                foreignTable: "experiment",
                localColumnNames: ["experiment_id"],
                foreignColumnNames: ["id"],
                options: ["onDelete" => "CASCADE"],
                constraintName: "FK_16A87BE3FF444C8"
            )
        ;

        //$this->addSql('ALTER TABLE experiment ADD CONSTRAINT FK_136F58B27E3C61F9 FOREIGN KEY (owner_id) REFERENCES user_accounts (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $schema->getTable("experiment")
            ->addForeignKeyConstraint(
                foreignTable: "user_accounts",
                localColumnNames: ["owner_id"],
                foreignColumnNames: ["id"],
                options: ["onDelete" => "CASCADE"],
                constraintName: "FK_136F58B27E3C61F9"
            )
        ;

        //$this->addSql('ALTER TABLE experiment ADD CONSTRAINT FK_136F58B2EB0F4B39 FOREIGN KEY (experiment_type_id) REFERENCES experiment_type (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $schema->getTable("experiment")
            ->addForeignKeyConstraint(
                foreignTable: "experiment_type",
                localColumnNames: ["experiment_type_id"],
                foreignColumnNames: ["id"],
                options: ["onDelete" => "CASCADE"],
                constraintName: "FK_136F58B2EB0F4B39"
            )
        ;

        //$this->addSql('ALTER TABLE experiment ADD CONSTRAINT FK_136F58B2C14C34C2 FOREIGN KEY (wellplate_id) REFERENCES culture_flask (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $schema->getTable("experiment")
            ->addForeignKeyConstraint(
                foreignTable: "experiment_type",
                localColumnNames: ["experiment_type_id"],
                foreignColumnNames: ["id"],
                options: ["onDelete" => "SET NULL"],
                constraintName: "FK_136F58B2EB0F4B39"
            )
        ;

        //$this->addSql('ALTER TABLE experiment_protein ADD CONSTRAINT FK_B6BB2618FF444C8 FOREIGN KEY (experiment_id) REFERENCES experiment (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $schema->getTable("experiment_protein")
            ->addForeignKeyConstraint(
                foreignTable: "experiment",
                localColumnNames: ["experiment_id"],
                foreignColumnNames: ["id"],
                options: ["onDelete" => "CASCADE"],
                constraintName: "FK_B6BB2618FF444C8"
            )
        ;

        //$this->addSql('ALTER TABLE experiment_protein ADD CONSTRAINT FK_B6BB261854985755 FOREIGN KEY (protein_id) REFERENCES protein (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $schema->getTable("experiment_protein")
            ->addForeignKeyConstraint(
                foreignTable: "protein",
                localColumnNames: ["protein_id"],
                foreignColumnNames: ["id"],
                options: ["onDelete" => "CASCADE"],
                constraintName: "FK_B6BB261854985755"
            )
        ;

        //$this->addSql('ALTER TABLE experiment_chemical ADD CONSTRAINT FK_B8F4E4F2FF444C8 FOREIGN KEY (experiment_id) REFERENCES experiment (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $schema->getTable("experiment_chemical")
            ->addForeignKeyConstraint(
                foreignTable: "experiment",
                localColumnNames: ["experiment_id"],
                foreignColumnNames: ["id"],
                options: ["onDelete" => "CASCADE"],
                constraintName: "FK_B8F4E4F2FF444C8"
            )
        ;

        //$this->addSql('ALTER TABLE experiment_chemical ADD CONSTRAINT FK_B8F4E4F2E1770A76 FOREIGN KEY (chemical_id) REFERENCES chemical (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $schema->getTable("experiment_chemical")
            ->addForeignKeyConstraint(
                foreignTable: "chemical",
                localColumnNames: ["chemical_id"],
                foreignColumnNames: ["id"],
                options: ["onDelete" => "CASCADE"],
                constraintName: "FK_B8F4E4F2E1770A76"
            )
        ;

        //$this->addSql('ALTER TABLE experiment_cell ADD CONSTRAINT FK_D078464FFF444C8 FOREIGN KEY (experiment_id) REFERENCES experiment (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $schema->getTable("experiment_cell")
            ->addForeignKeyConstraint(
                foreignTable: "experiment",
                localColumnNames: ["experiment_id"],
                foreignColumnNames: ["id"],
                options: ["onDelete" => "CASCADE"],
                constraintName: "FK_D078464FFF444C8"
            )
        ;

        //$this->addSql('ALTER TABLE experiment_cell ADD CONSTRAINT FK_D078464FCB39D93A FOREIGN KEY (cell_id) REFERENCES cell (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $schema->getTable("experiment_cell")
            ->addForeignKeyConstraint(
                foreignTable: "cell",
                localColumnNames: ["cell_id"],
                foreignColumnNames: ["id"],
                options: ["onDelete" => "CASCADE"],
                constraintName: "FK_D078464FCB39D93A"
            )
        ;

        //$this->addSql('ALTER TABLE experiment_type ADD CONSTRAINT FK_97219684B03A8386 FOREIGN KEY (created_by_id) REFERENCES user_accounts (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $schema->getTable("experiment_type")
            ->addForeignKeyConstraint(
                foreignTable: "user_accounts",
                localColumnNames: ["created_by_id"],
                foreignColumnNames: ["id"],
                options: ["onDelete" => "CASCADE"],
                constraintName: "FK_97219684B03A8386"
            )
        ;

        //$this->addSql('ALTER TABLE experiment_type ADD CONSTRAINT FK_97219684727ACA70 FOREIGN KEY (parent_id) REFERENCES experiment_type (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $schema->getTable("experiment_type")
            ->addForeignKeyConstraint(
                foreignTable: "experiment_type",
                localColumnNames: ["parent_id"],
                foreignColumnNames: ["id"],
                options: ["onDelete" => "SET NULL"],
                constraintName: "FK_97219684727ACA70"
            )
        ;

        //$this->addSql('ALTER TABLE experimental_condition ADD CONSTRAINT FK_6E25798CFF444C8 FOREIGN KEY (experiment_id) REFERENCES experiment (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $schema->getTable("experimental_condition")
            ->addForeignKeyConstraint(
                foreignTable: "experiment",
                localColumnNames: ["experiment_id"],
                foreignColumnNames: ["id"],
                options: ["onDelete" => "CASCADE"],
                constraintName: "FK_6E25798CFF444C8"
            )
        ;

        //$this->addSql('ALTER TABLE experimental_measurement ADD CONSTRAINT FK_AA3802E9FF444C8 FOREIGN KEY (experiment_id) REFERENCES experiment (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $schema->getTable("experimental_measurement")
            ->addForeignKeyConstraint(
                foreignTable: "experiment",
                localColumnNames: ["experiment_id"],
                foreignColumnNames: ["id"],
                options: ["onDelete" => "CASCADE"],
                constraintName: "FK_AA3802E9FF444C8"
            )
        ;
    }

    public function down(Schema $schema): void
    {
        throw new IrreversibleMigration();
    }
}
