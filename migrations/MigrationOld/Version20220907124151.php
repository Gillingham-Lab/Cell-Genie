<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Psr\Log\LoggerInterface;

final class Version20220907124151 extends AbstractMigration
{
    public function __construct(Connection $connection, LoggerInterface $logger)
    {
        $this->_logger = $logger;
        parent::__construct($connection, $logger);
    }

    public function getDescription(): string
    {
        return 'Moves antibody-lot association from antibody_lots to substance_lots';
    }

    private function getAntibodyLotsEntries(): array
    {
        $selectQuery = $this->connection->createQueryBuilder()
            ->select("x.antibody_ulid, x.lot_id")
            ->from("antibody_lots", "x");

        $this->_logger->debug($selectQuery->getSQL());

        return $selectQuery->fetchAllAssociative();
    }

    public function up(Schema $schema): void
    {
        $result = $this->getAntibodyLotsEntries();

        foreach ($result as $row) {
            $insertQuery = $this->connection->createQueryBuilder()
                ->insert("substance_lots")
                ->setValue("substance_ulid", ":substance")
                ->setValue("lot_id", ":lot")
                ->setParameter("substance", $row["antibody_ulid"], "ulid")
                ->setParameter("lot", $row["lot_id"], "ulid");

            $this->_logger->debug($insertQuery->getSQL());

            $insertQuery->executeQuery();

            $this->_logger->debug("Moved {$row['antibody_ulid']},{$row['lot_id']}.");
        }
    }

    public function down(Schema $schema): void
    {
        $result = $this->getAntibodyLotsEntries();

        foreach ($result as $row) {
            $insertQuery = $this->connection->createQueryBuilder()
                ->delete("substance_lots")
                ->where("substance_ulid = :substance")
                ->andWhere("lot_id = :lot")
                ->setParameter("substance", $row["antibody_ulid"], "ulid")
                ->setParameter("lot", $row["lot_id"], "ulid");

            $this->_logger->debug($insertQuery->getSQL());

            $insertQuery->executeQuery();

            $this->_logger->debug("Removed {$row['antibody_ulid']},{$row['lot_id']} from substance_lots.");
        }
    }
}
