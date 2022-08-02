<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220729094538 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $schema->getTable("epitope")->getColumn("epitope_type")->setLength(255);
        $schema->getTable("epitope")->addUniqueIndex(["short_name"], "UNIQ_6ED3F3363EE4B093");

        $schema->getTable("protein")->getColumn("short_name")->setLength(20);

        $table = $schema->getTable("protein_protein");
        $table->setPrimaryKey(["protein_parent_ulid", "protein_child_ulid"]);

        $table = $schema->getTable("recipe_ingredient");
        $this->addSql('ALTER TABLE recipe_ingredient DROP CONSTRAINT recipe_ingredient_pkey');
    }

    public function down(Schema $schema): void
    {
       $this->throwIrreversibleMigrationException();
    }
}
