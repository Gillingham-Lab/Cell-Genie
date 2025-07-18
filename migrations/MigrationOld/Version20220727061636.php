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
final class Version20220727061636 extends AbstractMigration
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
        # Get chemicals
        $result = $this->connection->createQueryBuilder()
            ->select("c.id, c.ulid")
            ->from("chemical", "c")
            ->fetchAllAssociative();

        $chemicalIdToUlid = [];
        foreach ($result as $row) {
            $chemicalIdToUlid[$row["id"]] = $row["ulid"];
        }

        # Update references
        foreach ($chemicalIdToUlid as $id => $ulid) {
            $updateQuery = $this->connection->createQueryBuilder()
                ->update("experiment_chemical")
                ->set("chemical_ulid", ":ulid")
                ->where("old_id = :id")
                ->setParameter("ulid", $ulid, "ulid")
                ->setParameter("id", $id, "integer");

            $this->_logger->debug($updateQuery->getSQL());
            $updateQuery->executeQuery();

            $updateQuery = $this->connection->createQueryBuilder()
                ->update("recipe_ingredient")
                ->set("chemical_ulid", ":ulid")
                ->where("old_id = :id")
                ->setParameter("ulid", $ulid, "ulid")
                ->setParameter("id", $id, "integer");

            $this->_logger->debug($updateQuery->getSQL());
            $updateQuery->executeQuery();
        }

        # Get proteins
        $result = $this->connection->createQueryBuilder()
            ->select("p.id, p.ulid")
            ->from("protein", "p")
            ->fetchAllAssociative();

        $proteinsToUlid = [];
        foreach ($result as $row) {
            $proteinsToUlid[$row["id"]] = $row["ulid"];
        }

        # Update references
        foreach ($proteinsToUlid as $id => $ulid) {
            $updateQuery = $this->connection->createQueryBuilder()
                ->update("experiment_protein")
                ->set("protein_ulid", ":ulid")
                ->where("old_id = :id")
                ->setParameter("ulid", $ulid, "ulid")
                ->setParameter("id", $id, "integer");

            $this->_logger->debug($updateQuery->getSQL());
            $updateQuery->executeQuery();

            $updateQuery = $this->connection->createQueryBuilder()
                ->update("antibody_protein")
                ->set("protein_ulid", ":ulid")
                ->where("old_id = :id")
                ->setParameter("ulid", $ulid, "ulid")
                ->setParameter("id", $id, "integer");

            $this->_logger->debug($updateQuery->getSQL());
            $updateQuery->executeQuery();
        }
    }

    public function down(Schema $schema): void {}
}
