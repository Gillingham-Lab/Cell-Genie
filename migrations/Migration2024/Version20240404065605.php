<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20240404065605 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a gene regulation field for cellular proteins';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("cell_protein");
        $table->addColumn("gene_regulation", Types::STRING)
            ->setLength(20)
            ->setNotnull(false);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("cell_protein");
        $table->dropColumn("gene_regulation");
    }
}
