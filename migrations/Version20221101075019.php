<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Ulid;

final class Version20221101075019 extends AbstractMigration
{
    public function __construct(
        $connection,
        private LoggerInterface $logger,
    ) {
        parent::__construct($connection, $this->logger);
    }

    public function getDescription(): string
    {
        return 'Data migration: Generates ulids for racks and boxes and updates existing references.';
    }

    private function getEntriesForTable($table, $additionalFields = []): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select("t.id")->from($table, "t");

        foreach ($additionalFields as $field) {
            $qb = $qb->addSelect("t." . $field);
        }

        return $qb->fetchAllAssociative();
    }

    private function updateTableEntry($table, $oldFields = [], $newFields = [])
    {
        assert(count($oldFields) === count($newFields));
        $oldFieldKeys = array_keys($oldFields);
        $oldFieldVals = array_values($oldFields);
        $newFieldKeys = array_keys($newFields);
        $newFieldVals = array_values($newFields);
        $numberOfFields = count($oldFields);

        $qb = $this->connection->createQueryBuilder()->update($table, "t");

        for ($i = 0; $i < $numberOfFields; $i++) {
            $oldField = $oldFieldKeys[$i];
            $oldFieldValue = $oldFieldVals[$i];
            $newField = $newFieldKeys[$i];
            $newFieldValue = $newFieldVals[$i];

            $qb->andWhere("t.{$oldField} = :{$oldField}");
            $qb->setParameter($oldField, $oldFieldValue, "integer");

            $qb->set("{$newField}", ":{$newField}");
            $qb->setParameter($newField, $newFieldValue, "ulid");
        }

        $query = $qb->getSQL();
        $this->logger->debug($query);

        $qb->executeQuery();
    }

    public function up(Schema $schema): void
    {
        $racks = $this->getEntriesForTable("rack");
        $rackIdToUlid = [];

        foreach ($racks as $rack) {
            $ulid = new Ulid();

            $rackIdToUlid[$rack["id"]] = $ulid;

            $this->logger->debug("Generate ulid for rack#{$rack['id']}: {$ulid}.");
            $this->updateTableEntry("rack", ["id" => $rack["id"]], ["ulid" => $ulid]);
            $this->updateTableEntry("box", ["rack_id" => $rack["id"]], ["rack_ulid" => $ulid]);
        }

        $boxes = $this->getEntriesForTable("box", ["rack_id"]);
        $boxIdToUlid = [];

        foreach ($boxes as $box) {
            $ulid = new Ulid();

            $boxIdToUlid[$box["id"]] = $ulid;
            $rackId = $box["rack_id"];

            if ($box["rack_id"] === null) {
                $this->updateTableEntry("box", ["id" => $box["id"]], ["ulid" => $ulid]);
                $this->logger->debug("Generate ulid for box#{$box['id']}: {$ulid}. Rack ref is null.");
            } else {
                $rackUlid = $rackIdToUlid[$box["rack_id"]];
                $this->logger->debug("Generate ulid for box#{$box['id']}: {$ulid}. Update rack ref from {$rackId} to {$rackUlid}.");
                $this->updateTableEntry("box", ["id" => $box["id"]], ["ulid" => $ulid]);
            }
        }

        foreach ($boxIdToUlid as $id => $ulid) {
            $this->updateTableEntry("cell_aliquote", ["box_id" => $id], ["box_ulid" => $ulid]);
            $this->updateTableEntry("lot", ["box_id" => $id], ["box_ulid" => $ulid]);
        }
    }

    public function down(Schema $schema): void
    {

    }
}
