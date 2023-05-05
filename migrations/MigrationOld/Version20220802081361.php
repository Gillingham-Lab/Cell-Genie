<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Psr\Log\LoggerInterface;

final class Version20220802081361 extends AbstractMigration
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
        // Now we need to update antibody_vendor_documentation_files with the new ulids.
        $result = $this->connection->createQueryBuilder()
            ->select("a.id, a.ulid")
            ->from("antibody", "a")
            ->fetchAllAssociative();

        $antibodyIdToUlid = [];
        foreach ($result as $row) {
            $antibodyIdToUlid[$row["id"]] = $row["ulid"];
        }

        # Update $antibodyIdToUlid
        foreach ($antibodyIdToUlid as $id => $ulid) {
            $updateQuery = $this->connection->createQueryBuilder()
                ->update("antibody_vendor_documentation_files")
                ->set("antibody_ulid",  ":ulid")
                ->where("antibody_id = :id")
                ->setParameter("ulid", $ulid, "ulid")
                ->setParameter("id", $id, "integer");

            $this->_logger->debug($updateQuery->getSQL());
            $updateQuery->executeQuery();
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
