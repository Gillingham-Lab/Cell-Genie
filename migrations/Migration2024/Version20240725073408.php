<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use App\Service\Doctrine\Type\Ulid;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Psr\Log\LoggerInterface;

final class Version20240725073408 extends AbstractMigration
{
    public function __construct(Connection $connection, private readonly LoggerInterface $logger)
    {
        parent::__construct($connection, $logger);
    }

    public function getDescription(): string
    {
        return 'Adds a ulid identifier to cells.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("cell");
        $table->addColumn("ulid", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");
        $table->addColumn("parent_ulid", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");

        $table = $schema->getTable("cell_protein");
        $table->addColumn("cell_line_ulid", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");

        $table = $schema->getTable("cell_aliquote");
        $table->addColumn("cell_ulid", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");

        $table = $schema->getTable("cell_file");
        $table->addColumn("cell_ulid", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("cell");
        $table->dropColumn("ulid");
        $table->dropColumn("parent_ulid");

        $table = $schema->getTable("cell_protein");
        $table->dropColumn("cell_line_ulid");

        $table = $schema->getTable("cell_aliquote");
        $table->dropColumn("cell_ulid");

        $table = $schema->getTable("cell_file");
        $table->dropColumn("cell_ulid");
    }

    public function postUp(Schema $schema): void
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $results = $queryBuilder
            ->from("cell", "cell")
            ->select("cell.id")
            ->orderBy("cell.id")
            ->fetchAllNumeric();

        $updateCellQuery = $this->connection->createQueryBuilder()->update("cell")->set("ulid", ":ulid")->where("id = :id");
        $updateCellParentQuery = $this->connection->createQueryBuilder()->update("cell")->set("parent_ulid", ":ulid")->where("parent_id = :id");
        $updateCellProteinQuery = $this->connection->createQueryBuilder()->update("cell_protein")->set("cell_line_ulid", ":ulid")->where("cell_line_id = :id");
        $updateCellALiquotQuery = $this->connection->createQueryBuilder()->update("cell_aliquote")->set("cell_ulid", ":ulid")->where("cell_id = :id");
        $updateCellFileQuery = $this->connection->createQueryBuilder()->update("cell_file")->set("cell_ulid", ":ulid")->where("cell_id = :id");

        $oldIdToUlid = [];
        $affectedRows = 0;
        $retrievedCells = 0;
        $affectedAssociations = 0;

        foreach ($results as $row) {
            $ulid = (new Ulid())->toRfc4122();

            $oldIdToUlid[$row[0]] = $ulid;

            $retrievedCells += 1;
            $affectedRows += $updateCellQuery->setParameters(["id" => $row[0], "ulid" => $ulid])->executeStatement();
            $affectedAssociations += $updateCellParentQuery->setParameters(["id" => $row[0], "ulid" => $ulid])->executeStatement();
            $affectedAssociations += $updateCellProteinQuery->setParameters(["id" => $row[0], "ulid" => $ulid])->executeStatement();
            $affectedAssociations += $updateCellALiquotQuery->setParameters(["id" => $row[0], "ulid" => $ulid])->executeStatement();
            $affectedAssociations += $updateCellFileQuery->setParameters(["id" => $row[0], "ulid" => $ulid])->executeStatement();

            $this->logger->debug($updateCellQuery->getSQL() . ", for id={$row[0]} and ulid={$ulid}");
        }

        $this->logger->info("Migrated {$affectedRows} out of {$retrievedCells} (and {$affectedAssociations} associations).");
    }
}
