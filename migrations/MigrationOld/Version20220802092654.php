<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Ulid;


final class Version20220802092654 extends AbstractMigration
{
    private LoggerInterface $logger;

    public function __construct(Connection $connection, LoggerInterface $logger)
    {
        $this->logger = $logger;

        parent::__construct($connection, $logger);
    }

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $conn = $this->connection;
        $conn->beginTransaction();

        // First, we get all host_target_ids of the antibodies.
        // For each pair of (antibody,host_target_id) we create a new entry in antibody_epitope.
        // This is only possible since we re-used the ID of AntibodyHost in EpitopeHost (which shares the ID with epitope!)
        //  antibody_host.id === epitope_host.id === epitope.id
        $result = $conn->createQueryBuilder()
            ->select("a.id, a.ulid, a.host_target_id")
            ->from("antibody", "a")
            ->fetchAllAssociative();

        $antibodyIdToUlid = [];
        foreach ($result as $antibody) {
            $antibodyIdToUlid[$antibody['id']] = $antibody["ulid"];

            $this->logger->debug("Antibody {$antibody['id']} has ulid {$antibody['ulid']}.");

            if (empty($antibody["host_target_id"])) {
                continue;
            }

            $insertQuery = $conn->createQueryBuilder()
                ->insert("antibody_epitope")
                ->setValue("antibody_ulid", ":antibody")
                ->setParameter("antibody", $antibody["ulid"], "ulid")
                ->setValue("epitope_id", ":epitope")
                ->setParameter("epitope", $antibody["host_target_id"], "ulid")
                ->executeQuery();

            $this->logger->debug("Migrated ({$antibody['ulid']}, {$antibody['host_target_id']}) to antibody_epitope");
        }

        // Secondly, we create for each protein a new epitope (and create another id => ulid map at the same time)
        $result = $conn->createQueryBuilder()
            ->select("p.id, p.ulid, p.short_name")
            ->from("protein", "p")
            ->fetchAllAssociative();

        $proteinIdToProteinUlid = [];
        $proteinUlidToProteinEpitopeUlid = [];
        foreach ($result as $protein) {
            $epitopeUlid = new Ulid();

            $proteinIdToProteinUlid[$protein["id"]] = $protein["ulid"];
            $proteinUlidToProteinEpitopeUlid[$protein["ulid"]] = $epitopeUlid;

            $short_name_result = $conn->createQueryBuilder()
                ->select("e.short_name, e.id")
                ->from("epitope", "e")
                ->where("e.short_name = :name")
                ->setParameter("name", $protein["short_name"])
                ->fetchAllAssociative();

            // If it already exists, we should get its ID :O
            if (count($short_name_result) > 0) {
                $proteinUlidToProteinEpitopeUlid[$protein["ulid"]] = Ulid::fromRfc4122($short_name_result[0]['id']);
                $this->logger->warning("Skipped entry for {$protein['short_name']}, there is already an epitope with that short name. Ulid is {$short_name_result[0]['id']}");
                continue;
            }

            $this->logger->debug("Protein {$protein['id']} has ulid {$protein['ulid']} and gets new epitope {$epitopeUlid->toRfc4122()}.");

            $conn->createQueryBuilder()
                ->insert("epitope")
                ->setValue("id", ":epitopeUlid")
                ->setParameter("epitopeUlid", $epitopeUlid, "ulid")
                ->setValue("short_name", ":shortName")
                ->setParameter("shortName", $protein["short_name"])
                ->setValue("epitope_type", ":type")
                ->setParameter("type", "epitopeprotein")
                ->executeQuery();

            // Then, we insert a row into epitope_protein
            $conn->createQueryBuilder()
                ->insert("epitope_protein")
                ->setValue("id", ":epitopeUlid")
                ->setParameter("epitopeUlid", $epitopeUlid, "ulid")
                ->executeQuery();

            // Then, we create an entry for epitope_protein_protein referencing epitope_protein and the protein table
            $conn->createQueryBuilder()
                ->insert("epitope_protein_protein")
                ->setValue("epitope_protein_id", ":epitopeUlid")
                ->setParameter("epitopeUlid", $epitopeUlid, "ulid")
                ->setValue("protein_ulid", ":proteinUlid")
                ->setParameter("proteinUlid", $protein["ulid"], "ulid")
                ->executeQuery();

            $this->logger->debug("Created epitope for {$protein['short_name']} ({$protein['ulid']} => {$epitopeUlid->toRfc4122()}).");
        }

        // Then, we fetch all entries from antibody_protein and create for each a new entry in antibody_epitope
        $result = $conn->createQueryBuilder()
            ->select("a.antibody_id, a.protein_ulid")
            ->from("antibody_protein", "a")
            ->fetchAllAssociative();

        foreach ($result as $row) {
            $epitopeUlid = $proteinUlidToProteinEpitopeUlid[$row["protein_ulid"]];
            $antibodyUlid = $antibodyIdToUlid[$row["antibody_id"]];

            $this->logger->debug("Create antibody-epitope for ({$row['antibody_id']}, {$row['protein_ulid']}) to ({$antibodyUlid}, {$epitopeUlid->toRfc4122()})");

            $conn->createQueryBuilder()
                ->insert("antibody_epitope")
                ->setValue("antibody_ulid", ":antibody")
                ->setParameter("antibody", $antibodyUlid, "ulid")
                ->setValue("epitope_id", ":epitope")
                ->setParameter("epitope", $epitopeUlid, "ulid")
                ->executeQuery();
        }

        $conn->commit();
    }

    public function down(Schema $schema): void
    {
    }
}
