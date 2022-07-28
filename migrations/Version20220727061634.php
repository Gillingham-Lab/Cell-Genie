<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Psr\Log\LoggerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220727061634 extends AbstractMigration
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
        # Create new keys
        $updateQuery = $this->connection->createQueryBuilder()
            ->update("experiment_protein")
            ->set("old_id", "protein_id");

        $this->_logger->debug($updateQuery->getSQL());
        $updateQuery->executeQuery();

        $updateQuery = $this->connection->createQueryBuilder()
            ->update("antibody_protein")
            ->set("old_id", "protein_id");

        $this->_logger->debug($updateQuery->getSQL());
        $updateQuery->executeQuery();

        $updateQuery = $this->connection->createQueryBuilder()
            ->update("experiment_chemical")
            ->set("old_id", "chemical_id");

        $this->_logger->debug($updateQuery->getSQL());
        $updateQuery->executeQuery();

        $updateQuery = $this->connection->createQueryBuilder()
            ->update("recipe_ingredient")
            ->set("old_id", "chemical_id");

        $this->_logger->debug($updateQuery->getSQL());
        $updateQuery->executeQuery();
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
