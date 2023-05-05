<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Psr\Log\LoggerInterface;

final class Version20220905142846 extends AbstractMigration
{
    public function __construct(Connection $connection, LoggerInterface $logger)
    {
        $this->_logger = $logger;
        parent::__construct($connection, $logger);
    }

    public function getDescription(): string
    {
        return 'Migrates IDs from chemical and protein to substance.';
    }

    public function up(Schema $schema): void
    {
        $fetchResults = function(string $table): array {
            $selectQuery = $this->connection->createQueryBuilder()
                ->select("a.ulid, a.short_name, a.long_name")
                ->from($table, "a");

            $this->_logger->debug($selectQuery->getSQL());

            $result = $selectQuery->fetchAllAssociative();

            return $result;
        };

        $migrateResult = function(array $rows, string $type): void {
            foreach ($rows as $row) {
                // Check if it already exists
                $selectQuery = $this->connection->createQueryBuilder()
                    ->select("s.short_name")
                    ->from("substance", "s")
                    ->where("s.short_name = :shortName")
                    ->setParameter("shortName", $row["short_name"]);
                $result = $selectQuery->fetchAllAssociative();

                if (count($result) > 0) {
                    $randomString = base64_encode(random_bytes(4));
                    $this->_logger->warning("{$row['short_name']} is non-unique; random element added ({$randomString}).");

                    $row["short_name"] .= " " . $randomString;
                }

                $updateQuery = $this->connection->createQueryBuilder()
                    ->insert("substance")
                    ->setValue("ulid",  ":param")
                    ->setValue("substance_type", ":type")
                    ->setValue("short_name", ":shortName")
                    ->setValue("long_name", ":longName")
                    ->setParameter("param", $row["ulid"], "ulid")
                    ->setParameter("shortName", $row["short_name"])
                    ->setParameter("longName", $row["long_name"])
                    ->setParameter("type", $type);

                $this->_logger->debug($updateQuery->getSQL());
                $updateQuery->executeQuery();
            }
        };

        // Migrate antibodies
        $result = $fetchResults("antibody");
        $migrateResult($result, "antibody");

        // Migrate chemicals
        $result = $fetchResults("chemical");
        $migrateResult($result, "chemical");

        // Migrate chemicals
        $result = $fetchResults("protein");
        $migrateResult($result, "protein");
    }

    public function down(Schema $schema): void
    {
        // Empty table again
        $query = $this->connection->createQueryBuilder()
            ->delete("substance");
        $this->_logger->debug($query->getSQL());
        $query->executeQuery();
    }
}
