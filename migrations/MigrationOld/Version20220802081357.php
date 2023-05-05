<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Ulid;

final class Version20220802081357 extends AbstractMigration
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
        $schema->dropSequence("antibody_id_seq");

        // Create new ulids
        $selectQuery = $this->connection->createQueryBuilder()
            ->select("a.id")
            ->from("antibody", "a");

        $this->_logger->debug($selectQuery->getSQL());

        $result = $selectQuery->fetchAllAssociative();

        foreach ($result as $row) {
            $ulid = new Ulid();

            $updateQuery = $this->connection->createQueryBuilder()
                ->update("antibody")
                ->set("ulid",  ":param")
                ->where("id = :id")
                ->setParameter("param", $ulid, "ulid")
                ->setParameter("id", $row["id"], "integer");

            $this->_logger->debug($updateQuery->getSQL());

            $updateQuery->executeQuery();

            $updateQuery = $this->connection->createQueryBuilder()
                ->update("antibody_lots")
                ->set("antibody_ulid",  ":param")
                ->where("antibody_id = :id")
                ->setParameter("param", $ulid, "ulid")
                ->setParameter("id", $row["id"], "integer");

            $this->_logger->debug($updateQuery->getSQL());

            $updateQuery->executeQuery();
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
