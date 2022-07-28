<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220727081017 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("antibody_protein");
        $table->addForeignKeyConstraint(foreignTable: "protein", localColumnNames: ["protein_ulid"], foreignColumnNames: ["ulid"], name: "FK_A4A178769926E711");
        $table->addIndex(columnNames: ["protein_ulid"], indexName: "IDX_A4A178769926E711");

        $table = $schema->getTable("experiment_protein");
        $table->addForeignKeyConstraint(foreignTable: "protein", localColumnNames: ["protein_ulid"], foreignColumnNames: ["ulid"], name: "FK_B6BB26189926E711");
        $table->addIndex(columnNames: ["protein_ulid"], indexName: "IDX_B6BB26189926E711");

        $table = $schema->getTable("experiment_chemical");
        $table->addForeignKeyConstraint(foreignTable: "chemical", localColumnNames: ["chemical_ulid"], foreignColumnNames: ["ulid"], name: "FK_B8F4E4F2325508D6");
        $table->addIndex(columnNames: ["chemical_ulid"], indexName: "IDX_B8F4E4F2325508D6");

        $table = $schema->getTable("recipe_ingredient");
        $table->addForeignKeyConstraint(foreignTable: "chemical", localColumnNames: ["chemical_ulid"], foreignColumnNames: ["ulid"], name: "FK_22D1FE13325508D6");
        $table->addIndex(columnNames: ["chemical_ulid"], indexName: "IDX_22D1FE13325508D6");

        $table = $schema->getTable("vocabulary");
        $table->setPrimaryKey(["id"]);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
