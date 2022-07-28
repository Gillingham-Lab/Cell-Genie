<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220727081018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $chemicalTable = $schema->getTable("chemical");
        $chemicalTable->getColumn("id")->setNotnull(false);

        $proteinTable = $schema->getTable("protein");
        $proteinTable->getColumn("id")->setNotnull(false);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
