<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220207133800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // $this->addSql('DROP SEQUENCE file_id_seq CASCADE');
        $schema->dropSequence("file_id_seq");

        /*
        $this->addSql('CREATE TABLE recipe (
            id UUID NOT NULL,
            concentration_factor DOUBLE PRECISION DEFAULT \'1\' NOT NULL,
            short_name VARCHAR(20) NOT NULL,
            long_name VARCHAR(255) NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DA88B1373EE4B093A3EE02C0 ON recipe (short_name, concentration_factor)');
        $this->addSql('COMMENT ON COLUMN recipe.id IS \'(DC2Type:ulid)\'');
         */
        $table = $schema->createTable("recipe");
        $table->addColumn('id', typeName: "guid")->setNotnull(true)->setComment("(DC2Type:ulid)");
        $table->addColumn("concentration_factor", typeName: "float")->setNotnull(true)->setDefault(1.0);
        $table->addColumn("short_name", "string")->setNotnull(true)->setLength(20);
        $table->addColumn("long_name", "string")->setNotnull(true)->setLength(255);
        $table->setPrimaryKey(["id"]);
        $table->addUniqueIndex(["short_name", "concentration_factor"], indexName: "UNIQ_DA88B1373EE4B093A3EE02C0");


        /*
        $this->addSql('CREATE TABLE recipe_ingredient (
            id UUID NOT NULL,
            recipe_id UUID DEFAULT NOT NULL,
            chemical_id INT DEFAULT NULL,
            concentration DOUBLE PRECISION DEFAULT \'0\' NOT NULL,
            concentration_unit VARCHAR(10) DEFAULT \'mol/L\' NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('COMMENT ON COLUMN recipe_ingredient.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN recipe_ingredient.recipe_id IS \'(DC2Type:ulid)\'');
        $this->addSql('CREATE INDEX IDX_22D1FE1359D8A214 ON recipe_ingredient (recipe_id)');
        $this->addSql('CREATE INDEX IDX_22D1FE13E1770A76 ON recipe_ingredient (chemical_id)');
        */
        $table = $schema->createTable("recipe_ingredient");
        $table->addColumn('id', typeName: "guid")->setNotnull(true)->setComment("(DC2Type:ulid)");
        $table->addColumn('recipe_id', typeName: "guid")->setNotnull(true)->setComment("(DC2Type:ulid)");
        $table->addColumn('chemical_id', typeName: "integer")->setNotnull(true);
        $table->addColumn("concentration", typeName: "float")->setNotnull(true)->setDefault(0.0);
        $table->addColumn("concentration_unit", typeName: "string")->setLength(10)->setNotnull(true)->setDefault("mol/L");
        $table->setPrimaryKey(["id"]);
        $table->addUniqueIndex(["recipe_id"], indexName: "IDX_22D1FE1359D8A214");
        $table->addUniqueIndex(["chemical_id"], indexName: "IDX_22D1FE13E1770A76");

        /*
        $this->addSql('ALTER TABLE recipe_ingredient ADD CONSTRAINT FK_22D1FE1359D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE recipe_ingredient ADD CONSTRAINT FK_22D1FE13E1770A76 FOREIGN KEY (chemical_id) REFERENCES chemical (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        */
        $table->addForeignKeyConstraint("recipe", ["recipe_id"], ["id"], name: "FK_22D1FE1359D8A214");
        $table->addForeignKeyConstraint("chemical", ["chemical_id"], ["id"], name: "FK_22D1FE13E1770A76");

        /*
        $this->addSql('ALTER TABLE antibody ALTER storage_temperature SET DEFAULT 0');
        $this->addSql('ALTER TABLE antibody_host ALTER name TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE antibody_host ALTER name DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN antibody_host.name IS NULL');
        */
        $table = $schema->getTable("antibody");
        $table->getColumn("storage_temperature")->setDefault("0");

        $table = $schema->getTable("antibody_host");
        $table->getColumn("name")->setDefault(null)->setType(Type::getType("string"))->setLength(255)->setComment(null);

        /*
        $this->addSql('ALTER TABLE chemical ADD molecular_mass DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE chemical ADD density DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE chemical ADD cas_number VARCHAR(255) DEFAULT NULL');
        */
        $table = $schema->getTable("chemical");
        $table->addColumn("molecular_mass", "float")->setNotnull(true)->setDefault(0.0);
        $table->addColumn("density", "float")->setNotnull(false);
        $table->addColumn("cas_number", "string")->setLength(255)->setNotnull(false);

        /*
        $this->addSql('ALTER TABLE file_blob ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE file_blob ALTER id DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN file_blob.id IS \'(DC2Type:ulid)\'');
        */
        $table = $schema->getTable("file_blob");
        $table->getColumn("id")->setType(Type::getType("guid"))->setNotnull(true)->setDefault(null)->setComment("(DC2Type:ulid)");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
