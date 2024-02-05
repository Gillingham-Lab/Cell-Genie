<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20240205084458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $vendorTable = $schema->getTable("vendor");
        $vendorTable->addColumn("homepage", Types::STRING)
            ->setLength(255)
            ->setNotnull(false);
    }

    public function down(Schema $schema): void
    {
        $vendorTable = $schema->getTable("vendor");
        $vendorTable->dropColumn("homepage");
    }
}
