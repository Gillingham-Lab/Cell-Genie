<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20240719081917 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds the exposed field to experimental design field tables.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("new_experimental_design_field");
        $table->addColumn("exposed", Types::BOOLEAN)->setNotnull(true)->setDefault(false);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("new_experimental_design_field");
        $table->dropColumn("exposed");
    }
}
