<?php
declare(strict_types=1);

namespace DoctrineMigrations2025;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250701113542 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds an active flag to instruments.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('instrument');
        $table->addColumn('active', 'boolean')->setNotnull(true)->setDefault(true);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('instrument');
        $table->dropColumn('active');
    }
}
