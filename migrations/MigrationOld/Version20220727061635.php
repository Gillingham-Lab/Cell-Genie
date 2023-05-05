<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220727061635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        # Create new column and drop old id
        $table = $schema->getTable("experiment_protein");
        $table->addColumn("protein_ulid", "guid")
            ->setNotnull(false)
            ->setComment("(DC2Type:ulid)");

        $table = $schema->getTable("antibody_protein");
        $table->addColumn("protein_ulid", "guid")
            ->setNotnull(false)
            ->setComment("(DC2Type:ulid)");

        $table = $schema->getTable("experiment_chemical");
        $table->addColumn("chemical_ulid", "guid")
            ->setNotnull(false)
            ->setComment("(DC2Type:ulid)");

        $table = $schema->getTable("recipe_ingredient");
        $table->addColumn("chemical_ulid", "guid")
            ->setNotnull(false)
            ->setComment("(DC2Type:ulid)");
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("experiment_protein")->dropColumn("protein_ulid");
        $table = $schema->getTable("antibody_protein")->dropColumn("protein_ulid");
        $table = $schema->getTable("experiment_chemical")->dropColumn("chemical_ulid");
        $table = $schema->getTable("recipe_ingredient")->dropColumn("chemical_ulid");
    }
}
