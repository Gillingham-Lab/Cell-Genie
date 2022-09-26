<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Psr\Log\LoggerInterface;

final class Version20220921143129 extends AbstractMigration
{
    public function __construct(Connection $connection, private LoggerInterface $logger)
    {
        $this->logger = $logger;
        parent::__construct($connection, $logger);
    }

    public function getDescription(): string
    {
        return 'Migrates epitopes relations to main table.';
    }

    public function up(Schema $schema): void
    {
        $preparedInsertSQL = $this->connection->createQueryBuilder()
            ->insert("substance_epitopes")
            ->values(["substance_ulid" => ":substance", "epitope_id" => ":epitope"])
            ->getSQL();

        $preparedInsert = $this->connection->prepare($preparedInsertSQL);

        $resultSet = [];

        $resultSet["protein"] = $this->getEntriesFrom("epitope_protein_protein", "epitope_protein_id", "protein_ulid");
        $resultSet["chemical"] = $this->getEntriesFrom("epitope_small_molecule_chemical", "epitope_small_molecule_id", "chemical_ulid");
        $resultSet["antibodies"] = $this->getAntibodyEntries();


        foreach ($resultSet as $set => $results) {
            $this->logger->info("Migration epitopes of {$set}.");

            $i = 0;
            foreach ($results as $result) {

                if ($result["epitope_id"] === null) {
                    $this->logger->debug("sub: {$result["substance_ulid"]} skipped (no host organism registered).");
                    continue;
                } else {
                    $this->logger->debug("sub: {$result["substance_ulid"]}; ep: {$result["epitope_id"]}");
                }

                $preparedInsert->bindValue("substance", $result["substance_ulid"], "ulid");
                $preparedInsert->bindValue("epitope", $result["epitope_id"], "ulid");
                $preparedInsert->executeStatement();
                $i++;
            }

            $this->logger->info("Migration epitopes of {$set}: {$i} migrated.");
        }
    }

    public function down(Schema $schema): void
    {
        $this->logger->info("Removing all epitopes from substance_epitopes.");

        $this->connection->createQueryBuilder()
            ->delete("substance_epitopes", "s")
            ->executeStatement();
    }

    private function getEntriesFrom(string $table, string $field1, string $field2): array {
        return $this->connection->createQueryBuilder()
            ->select("sub.$field1 AS epitope_id")
            ->addSelect("sub.$field2 AS substance_ulid")
            ->from($table, "sub")
            ->fetchAllAssociative();
    }

    private function getAntibodyEntries(): array {
        return $this->connection->createQueryBuilder()
            ->select("ab.ulid AS substance_ulid")
            ->addSelect("ab.host_organism_id AS epitope_id")
            ->from("antibody", "ab")
            ->fetchAllAssociative();
    }
}
