<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220804053923 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable("cell_protein");
        $table->addColumn("id", "guid")
            ->setNotnull(true)
            ->setComment("(DC2Type:ulid)");
        $table->addColumn("cell_line_id", "integer")
            ->setNotnull(true);
        $table->addColumn("associated_protein_id", "guid")
            ->setNotnull(true)
            ->setComment("(DC2Type:ulid)");
        $table->addColumn("description", "text")
            ->setNotnull(false);
        $table->addColumn("detection", "text")
            ->setNotnull(false)
            ->setComment("(DC2Type:array)");

        $table->setPrimaryKey(["id"]);

        $table->addForeignKeyConstraint("cell", ["cell_line_id"], ["id"], ["onDelete" => "CASCADE"], "FK_FD840B0528079FF5");
        $table->addForeignKeyConstraint("protein", ["associated_protein_id"], ["ulid"], ["onDelete" => "CASCADE"], "FK_FD840B05BF2F7614");
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable("cell_protein");
    }
}
