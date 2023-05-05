<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221014072359 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Changes cellAliquote.cellCount to be nullable.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("cell_aliquote");
        $table->getColumn("cell_count")
            ->setDefault(null)
            ->setNotnull(false);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("cell_aliquote");
        $table->getColumn("cell_count")
            ->setDefault(0)
            ->setNotnull(true);
    }
}
