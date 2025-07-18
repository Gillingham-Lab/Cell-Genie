<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220802092653 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Create new relationship table antibody->epitope
        $table = $schema->createTable("antibody_epitope");
        $table->addColumn("antibody_ulid", "guid")
            ->setNotnull(true)
            ->setComment("(DC2Type:ulid)");
        $table->addColumn("epitope_id", "guid")
            ->setNotnull(true)
            ->setComment("(DC2Type:ulid)");

        $table->setPrimaryKey(["antibody_ulid", "epitope_id"]);
        $table->addIndex(["antibody_ulid"], "IDX_528A6AF2F7E9411");
        $table->addIndex(["epitope_id"], "IDX_528A6AF266EC6D6C");

        $table->addForeignKeyConstraint("antibody", ["antibody_ulid"], ["ulid"], ["onDelete" => "CASCADE"], "FK_528A6AF2F7E9411");
        $table->addForeignKeyConstraint("epitope", ["epitope_id"], ["id"], ["onDelete" => "CASCADE"], "FK_528A6AF266EC6D6C");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
