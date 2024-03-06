<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20240306072248 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds an aliquot name field to cell aliquots';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("cell_aliquote");
        $table->addColumn("aliquot_name", Types::STRING)
            ->setLength(30)
            ->setNotnull(false);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("cell_aliquote");
        $table->dropColumn("aliquot_name");
    }
}
