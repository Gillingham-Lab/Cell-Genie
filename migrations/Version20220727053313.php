<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Repository\ChemicalRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Ulid;


final class Version20220727053313 extends AbstractMigration
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
        $selectQuery = $this->connection->createQueryBuilder()
            ->select("c.id")
            ->from("chemical", "c");

        $this->_logger->debug($selectQuery->getSQL());

        $result = $selectQuery->fetchAllAssociative();

        foreach ($result as $row) {
            $ulid = new Ulid();

            $updateQuery = $this->connection->createQueryBuilder()
                ->update("chemical")
                ->set("ulid",  ":param")
                ->where("id = :id")
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
