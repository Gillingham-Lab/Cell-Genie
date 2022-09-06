<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Psr\Log\LoggerInterface;


final class Version20220727053312 extends AbstractMigration
{
    public function __construct(Connection $connection, LoggerInterface $logger)
    {
        $this->_logger = $logger;
        parent::__construct($connection, $logger);
    }

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Migration of numeric id to guid id for chemicals
        $chemicalTable = $schema->getTable("chemical");

        $chemicalTable->addColumn("ulid", "guid")
            ->setNotnull(false)
            ->setComment("(DC2Type:ulid)");

        $schema->dropSequence("chemical_id_seq");


        // Migration of numeric id to guid id for chemicals
        $proteinTable = $schema->getTable("protein");

        $proteinTable->addColumn("ulid", "guid")
            ->setNotnull(false)
            ->setComment("(DC2Type:ulid)");

        $schema->dropSequence("protein_id_seq");
    }

    public function down(Schema $schema): void
    {
        $schema->getTable("chemical")->dropColumn("ulid");
        $schema->getTable("protein")->dropColumn("ulid");
    }
}
