<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use App\Entity\FormEntity\DetectionEntry;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Dunglas\DoctrineJsonOdm\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

final class Version20240404050248 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Part 1 of the migration of array-to-json conversion for cell proteins. This creates a temporary column and copies the data into the copy and empties the original column.';
    }

    private array $data = [];

    public function preUp(Schema $schema): void
    {
        $connection = $this->connection;
        $rows = $connection->fetchAllAssociative("SELECT * FROM cell_protein");

        foreach ($rows as $row) {
            $data = unserialize($row["detection"]);
            $this->data[$row["id"]] = $data;
        }
    }

    public function up(Schema $schema): void
    {
        // Make a second column (detection_b) to copy serialized data into
        $table = $schema->getTable("cell_protein");
        $table->addColumn("detection_b", Types::JSON)
            ->setNotnull(false)
            ->setComment("(DC2Type:json_document)")
            ->setPlatformOption("jsonb", true)
        ;
        $table->dropColumn("detection");
    }

    public function postUp(Schema $schema): void
    {
        $connection = $this->connection;
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);

        foreach ($this->data as $id => $data) {
            $json = $serializer->serialize($data, "json");

            $connection->executeStatement("UPDATE cell_protein SET detection_b = :jsonData WHERE id = :id", [
                "id" => $id,
                "jsonData" => $json,
            ]);
        }
    }

    public function preDown(Schema $schema): void
    {
        $connection = $this->connection;
        $serializer = new Serializer([new ObjectNormalizer(), new ArrayDenormalizer()], [new JsonEncoder()]);

        $rows = $connection->fetchAllAssociative("SELECT * FROM cell_protein");

        foreach ($rows as $row) {
            $data = $serializer->deserialize($row["detection_b"], DetectionEntry::class . "[]", "json");
            $this->data[$row["id"]] = $data;
        }
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("cell_protein");
        $table->dropColumn("detection_b");
        $table->addColumn("detection", Types::ARRAY)
            ->setNotnull(false)
            ->setComment("(DC2Type:array)")
        ;
    }

    public function postDown(Schema $schema): void
    {
        $connection = $this->connection;

        foreach ($this->data as $id => $data) {
            $serializedData = serialize($data);

            $connection->executeStatement("UPDATE cell_protein SET detection = :serializedData WHERE id = :id", [
                "id" => $id,
                "serializedData" => $serializedData,
            ]);
        }
    }
}
