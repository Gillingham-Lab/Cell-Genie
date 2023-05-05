<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220615062906 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("cell_aliquote")
            ->getColumn("passage")->setNotnull(false)->setDefault(null);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("cell_aliquote")
            ->getColumn("passage")->setNotnull(true)->setDefault(0);
    }
}
