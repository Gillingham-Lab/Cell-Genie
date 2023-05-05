<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220803060801 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("protein");
        $table->addColumn("protein_type", "string")
            ->setNotnull(false)
            ->setLength(255);
        $table->addColumn("mutation", "string")
            ->setNotnull(false)
            ->setLength(255);
        $table->addColumn("fasta_sequence", "text")
            ->setNotnull(false);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
