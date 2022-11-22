<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221122070412 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds an organism column to proteins.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("protein");
        $table->addColumn("organism_id", "integer")->setNotnull(false);
        $table->addForeignKeyConstraint("organism", ["organism_id"], ["id"], ["onDelete" => "SET NULL"], "FK_98F8E1B264180A36");
        $table->addIndex(["organism_id"], "IDX_98F8E1B264180A36");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
