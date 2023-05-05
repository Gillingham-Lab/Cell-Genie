<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220727061630 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $chemicalTable = $schema->getTable("chemical");
        $chemicalTable->addUniqueIndex(["id"], "UNIQ_8ED9EDC3BF396750");
        $chemicalTable->getColumn("ulid")->setNotnull(true);

        $proteinTable = $schema->getTable("protein");
        $proteinTable->addUniqueIndex(["id"], "UNIQ_98F8E1B2BF396750");
        $proteinTable->getColumn("ulid")->setNotnull(true);

        $logTable = $schema->getTable("ext_log_entries");
        $logTable->getColumn("id")->setDefault(null);
        $logTable->setPrimaryKey(["id"]);

        $cellAliquoteTable = $schema->getTable("cell_aliquote");
        $cellAliquoteTable->getColumn("mycoplasma_tested_by_id")->setComment("(DC2Type:ulid)");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
