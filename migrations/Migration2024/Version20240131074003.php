<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20240131074003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a comment and a pin-code field to racks';
    }

    public function up(Schema $schema): void
    {
        $rack = $schema->getTable("rack");
        $rack->addColumn("pin_code", Types::STRING)
            ->setNotnull(false)
            ->setLength(100);
        $rack->addColumn("comment", Types::TEXT)
            ->setNotnull(false);
    }

    public function down(Schema $schema): void
    {
        $rack = $schema->getTable("rack");
        $rack->dropColumn("pin_code");
        $rack->dropColumn("comment");
    }
}
