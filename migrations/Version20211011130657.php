<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211011130657 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        /*
        $this->addSql('CREATE TABLE experimental_run (
            id UUID NOT NULL,
            experiment_id UUID DEFAULT NULL,
            owner_id UUID NOT NULL,
            name VARCHAR(255) NOT NULL,
            number_of_wells INT DEFAULT 1 NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            modified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            PRIMARY KEY(id))
           ');
        $this->addSql('CREATE INDEX IDX_30B5493EFF444C8 ON experimental_run (experiment_id)');
        $this->addSql('CREATE INDEX IDX_30B5493E7E3C61F9 ON experimental_run (owner_id)');
        $this->addSql('COMMENT ON COLUMN experimental_run.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN experimental_run.experiment_id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN experimental_run.owner_id IS \'(DC2Type:ulid)\'');
        */
        $table = $schema->createTable("experimental_run");
        $table->addColumn("id", "guid")->setNotnull(true)
            ->setComment("(DC2Type:ulid)");
        $table->addColumn("experiment_id", "guid")->setNotnull(false)->setDefault(null)
            ->setComment("(DC2Type:ulid)");
        $table->addColumn("owner_id", "guid")->setNotnull(true)
            ->setComment("(DC2Type:ulid)");
        $table->addColumn("name", "string")->setNotnull(true)->setLength(255);
        $table->addColumn("number_of_wells", "integer")->setNotnull(true)->setDefault(1);
        $table->addColumn("created_at", "datetime")->setNotnull(false);
        $table->addColumn("modified_at", "datetime")->setNotnull(false);

        $table->setPrimaryKey(["id"]);
        $table->addIndex(["experiment_id"], indexName: "IDX_30B5493EFF444C8");
        $table->addIndex(["owner_id"], indexName: "IDX_30B5493E7E3C61F9");

        /*
        $this->addSql('CREATE TABLE experimental_run_well (
            id UUID NOT NULL,
            experimental_run_id UUID DEFAULT NULL,
            well_number INT NOT NULL,
            well_name VARCHAR(255) NOT NULL,
            well_data TEXT DEFAULT \'a:0:{}\' NOT NULL,
            is_external_standard BOOLEAN DEFAULT \'false\' NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_AD072C69D204BED5 ON experimental_run_well (experimental_run_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AD072C69D204BED5448CD945 ON experimental_run_well (experimental_run_id, well_number)');
        $this->addSql('COMMENT ON COLUMN experimental_run_well.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN experimental_run_well.experimental_run_id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN experimental_run_well.well_data IS \'(DC2Type:array)\'');*/
        $table = $schema->createTable("experimental_run_well");
        $table->addColumn("id", "guid")->setNotnull(true)
            ->setComment("(DC2Type:ulid)");
        $table->addColumn("experimental_run_id", "guid")->setNotnull(true)
            ->setComment("(DC2Type:ulid)");
        $table->addColumn("well_number", "integer")->setNotnull(true);
        $table->addColumn("well_name", "string")->setLength(255)->setNotnull(true);
        $table->addColumn("well_data", "text")->setDefault("a:0:{}")->setNotnull(true)
            ->setComment("(DC2Type:array)");
        $table->addColumn("is_external_standard", "boolean")->setDefault(false)->setNotnull(true);

        $table->setPrimaryKey(["id"]);
        $table->addIndex(["experimental_run_id"], indexName: "IDX_AD072C69D204BED5");
        $table->addUniqueIndex(["experimental_run_id", "well_number"], indexName: "UNIQ_AD072C69D204BED5448CD945");

        /*
        $this->addSql('ALTER TABLE experimental_run ADD CONSTRAINT FK_30B5493EFF444C8 FOREIGN KEY (experiment_id) REFERENCES experiment (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE experimental_run ADD CONSTRAINT FK_30B5493E7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user_accounts (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE experimental_run_well ADD CONSTRAINT FK_AD072C69D204BED5 FOREIGN KEY (experimental_run_id) REFERENCES experimental_run (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        */
        $table = $schema->getTable("experimental_run");
        $table->addForeignKeyConstraint(
            foreignTable: "experiment",
            localColumnNames: ["experiment_id"],
            foreignColumnNames: ["id"],
            options: ["onDelete" => "CASCADE"],
            constraintName: "FK_30B5493EFF444C8",
        );
        $table->addForeignKeyConstraint(
            foreignTable: "user_accounts",
            localColumnNames: ["owner_id"],
            foreignColumnNames: ["id"],
            options: ["onDelete" => "CASCADE"],
            constraintName: "FK_30B5493E7E3C61F9",
        );

        $table = $schema->getTable("experimental_run_well");
        $table->addForeignKeyConstraint(
            foreignTable: "experimental_run",
            localColumnNames: ["experimental_run_id"],
            foreignColumnNames: ["id"],
            options: ["onDelete" => "CASCADE"],
            constraintName: "FK_AD072C69D204BED5",
        );

        /*
        $this->addSql('ALTER TABLE experiment ADD number_of_wells INT DEFAULT 1 NOT NULL');
        */
        $table = $schema->getTable("experiment");
        $table->addColumn("number_of_wells", "integer")->setDefault(1)->setNotnull(true);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable("experimental_run_well");
        $schema->dropTable("experimental_run");

        $schema->getTable("experiment")->dropColumn("number_of_wells");
    }
}
