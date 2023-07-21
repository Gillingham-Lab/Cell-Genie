<?php
declare(strict_types=1);

namespace DoctrineMigrations2023;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20230721060555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a description field for the box entity.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("box");
        $table->addColumn("description", Types::TEXT)->setNotnull(false);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("box");
        $table->dropColumn("description");
    }
}
