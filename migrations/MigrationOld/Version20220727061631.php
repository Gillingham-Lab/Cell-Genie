<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220727061631 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE experiment_protein DROP CONSTRAINT experiment_protein_pkey');
        $this->addSql('ALTER TABLE antibody_protein DROP CONSTRAINT antibody_protein_pkey');
        $this->addSql('ALTER TABLE experiment_chemical DROP CONSTRAINT experiment_chemical_pkey');
        $this->addSql('ALTER TABLE recipe_ingredient DROP CONSTRAINT recipe_ingredient_pkey');

        # Create tables to remember old identifier
        # and remove foreign key constraints
        $table = $schema->getTable("experiment_protein");
        $table->addColumn("old_id", "integer")->setNotnull(false);
        $table->getColumn("protein_id")->setNotnull(false)->setDefault(null);
        $table->removeForeignKey("fk_b6bb261854985755");

        $table = $schema->getTable("antibody_protein");
        $table->addColumn("old_id", "integer")->setNotnull(false);
        $table->getColumn("protein_id")->setNotnull(false)->setDefault(null);
        $table->removeForeignKey("fk_a4a1787654985755");

        $table = $schema->getTable("experiment_chemical");
        $table->addColumn("old_id", "integer")->setNotnull(false);
        $table->getColumn("chemical_id")->setNotnull(false)->setDefault(null);
        $table->removeForeignKey("fk_b8f4e4f2e1770a76");

        $table = $schema->getTable("recipe_ingredient");
        $table->addColumn("old_id", "integer")->setNotnull(false);
        $table->getColumn("chemical_id")->setNotnull(false)->setDefault(null);
        $table->removeForeignKey("fk_22d1fe13e1770a76");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
