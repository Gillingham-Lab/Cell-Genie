<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use App\Service\Doctrine\Type\Ulid;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Psr\Log\LoggerInterface;

final class Version20240730060724 extends AbstractMigration
{
    private $cellCultures = [];

    public function __construct(Connection $connection, private readonly LoggerInterface $logger)
    {
        parent::__construct($connection, $logger);
    }

    public function getDescription(): string
    {
        return 'Step 1: Cell aliquot migration to use UUID instead of SEQUENCE';
    }

    public function up(Schema $schema): void
    {
        $newAliquotTable = $schema->createTable("cell_aliquot");
        $newAliquotTable->addColumn('id', Types::GUID)->setComment("(DC2Type:ulid)")->setNotnull(true);
        $newAliquotTable->addColumn('parent_aliquot_id', Types::GUID)->setComment("(DC2Type:ulid)")->setNotnull(false);
        $newAliquotTable->addColumn("aliquoted_by_id", Types::GUID)->setComment("(DC2Type:ulid)")->setNotnull(false);
        $newAliquotTable->addColumn("mycoplasma_tested_by_id", Types::GUID)->setComment("(DC2Type:ulid)")->setNotnull(false);
        $newAliquotTable->addColumn("cell_id", Types::GUID)->setComment("(DC2Type:ulid)")->setNotnull(true);
        $newAliquotTable->addColumn("box_ulid", Types::GUID)->setComment("(DC2Type:ulid)")->setNotnull(false);
        $newAliquotTable->addColumn("owner_id", Types::GUID)->setComment("(DC2Type:ulid)")->setNotnull(false);
        $newAliquotTable->addColumn("group_id", Types::GUID)->setComment("(DC2Type:ulid)")->setNotnull(false);
        $newAliquotTable->addColumn("aliquoted_on", Types::DATETIME_MUTABLE)->setNotnull(false);
        $newAliquotTable->addColumn("vial_color", Types::STRING)->setLength(30)->setNotnull(true);
        $newAliquotTable->addColumn("vials", Types::INTEGER)->setNotnull(true);
        $newAliquotTable->addColumn("max_vials", Types::INTEGER)->setNotnull(false)->setDefault(0);
        $newAliquotTable->addColumn("passage", Types::INTEGER)->setNotnull(false);
        $newAliquotTable->addColumn("passage_detail", Types::STRING)->setLength(255)->setNotnull(false);
        $newAliquotTable->addColumn("cell_count", Types::INTEGER)->setNotnull(false);
        $newAliquotTable->addColumn("mycoplasma_tested_on", Types::DATETIME_MUTABLE)->setNotnull(false);
        $newAliquotTable->addColumn("mycoplasma_result", Types::STRING)->setLength(255)->setNotnull(true)->setDefault("unknown");
        $newAliquotTable->addColumn("mycoplasma", Types::TEXT)->setNotnull(false);
        $newAliquotTable->addColumn("typing", Types::TEXT)->setNotnull(false);
        $newAliquotTable->addColumn("history", Types::TEXT)->setNotnull(false);
        $newAliquotTable->addColumn("cryo_medium", Types::STRING)->setLength(255)->setNotnull(false);
        $newAliquotTable->addColumn("box_coordinate", Types::STRING)->setLength(10)->setNotnull(false);
        $newAliquotTable->addColumn("aliquot_name", Types::STRING)->setLength(30)->setNotnull(false);
        $newAliquotTable->addColumn("privacy_level", Types::SMALLINT)->setNotnull(true)->setDefault(2);

        $newAliquotTable->setPrimaryKey(['id']);
        $newAliquotTable->addIndex(["aliquoted_by_id"], "IDX_A71078AC88E0642");
        $newAliquotTable->addIndex(["mycoplasma_tested_by_id"], "IDX_A71078A20E05D61");
        $newAliquotTable->addIndex(["cell_id"], "IDX_A71078ACB39D93A");
        $newAliquotTable->addIndex(["parent_aliquot_id"], "IDX_A71078A98D0C6D0");
        $newAliquotTable->addIndex(["box_ulid"], "IDX_A71078A34EC8450");
        $newAliquotTable->addIndex(["owner_id"], "IDX_A71078A7E3C61F9");
        $newAliquotTable->addIndex(["group_id"], "IDX_A71078AFE54D947");

        $newAliquotTable->addForeignKeyConstraint("user_accounts", ["aliquoted_by_id"], ["id"], ["onDelete" => "SET NULL"], "FK_A71078AC88E0642");
        $newAliquotTable->addForeignKeyConstraint("user_accounts", ["mycoplasma_tested_by_id"], ["id"], ["onDelete" => "SET NULL"], "FK_A71078A20E05D61");
        $newAliquotTable->addForeignKeyConstraint("cell", ["cell_id"], ["id"], ["onDelete" => "CASCADE"], "FK_A71078ACB39D93A");
        $newAliquotTable->addForeignKeyConstraint("cell_aliquot", ["parent_aliquot_id"], ["id"], ["onDelete" => "SET NULL"], "FK_A71078A98D0C6D0");
        $newAliquotTable->addForeignKeyConstraint("box", ["box_ulid"], ["ulid"], ["onDelete" => "SET NULL"], "FK_A71078A34EC8450");
        $newAliquotTable->addForeignKeyConstraint("user_accounts", ["owner_id"], ["id"], ["onDelete" => "SET NULL"], "FK_A71078A7E3C61F9");
        $newAliquotTable->addForeignKeyConstraint("user_group", ["group_id"], ["id"], ["onDelete" => "SET NULL"], "FK_A71078AFE54D947");

        $cellCultureTable = $schema->getTable("cell_culture");

        // Manually drop it or doctrine will prefer ALTER
        $this->addSql("ALTER TABLE cell_culture DROP aliquot_id");
        $this->addSql("ALTER TABLE cell_culture ADD aliquot_id uuid DEFAULT NULL");
        $this->addSql("COMMENT ON COLUMN cell_culture.aliquot_id IS '(DC2Type:ulid)'");
    }

    public function preUp(Schema $schema): void
    {
        $cellCultures = $this->connection->createQueryBuilder()
            ->select("id")
            ->addSelect("aliquot_id")
            ->from("cell_culture")
            ->fetchAllAssociative()
        ;

        $this->cellCultures = $cellCultures;
        $nulls = $this->connection->createQueryBuilder()->update("cell_culture")->set("aliquot_id", "?")->setParameter(0, null)->executeStatement();
        $this->logger->info("Removed cell aliquotes from $nulls cell culture rows.");
    }

    public function postUp(Schema $schema): void
    {
        // Get all aliquots
        $query = $this->connection->createQueryBuilder()
            ->select("*")
            ->from("cell_aliquote")
            ->orderBy("parent_aliquot_id", "ASC NULLS FIRST")
            ->fetchAllAssociative();

        $insertCellAliquoteQuery = $this->connection->createQueryBuilder()
            ->insert("cell_aliquot");

        $this->logger->info(count($query) . " cell aliquots found.");

        $idToUlid = [];
        $first = true;
        $rows = 0;

        // Loop a first time to create the new ids.
        foreach ($query as $row) {
            if ($first) {
                // Modify the insert query to set all the values
                foreach (array_keys($row) as $columnName) {
                    $insertCellAliquoteQuery = $insertCellAliquoteQuery
                        ->setValue("$columnName", ":$columnName");
                }
                $first = false;
            }

            // Create a new ulid
            $ulid = (new Ulid())->toRfc4122();

            $idToUlid[$row["id"]] = $ulid;
            $this->logger->debug("CellAliquot:{$row['id']} => {$ulid}");
        }

        // Loop a second time to assign them.
        foreach ($query as $row) {
            // overwrite new ID
            $oldId = $row["id"];
            $row["id"] = $idToUlid[$row["id"]];

            // Overwrite parent ID. Since all "null" parents are on top, this should work in most of the cases.
            if ($row["parent_aliquot_id"] !== null) {
                $row["parent_aliquot_id"] = $idToUlid[$row["parent_aliquot_id"]];
            }

            $rowQuery = $insertCellAliquoteQuery->setParameters($row);
            $affectedRows = $rowQuery->executeStatement();

            if ($affectedRows) {
                $this->logger->debug($insertCellAliquoteQuery->getSQL() . ", for id={$oldId} and ulid={$row["id"]}");
            }
            $rows++;

        }

        $this->logger->info("$rows cell aliquots updated.");

        $updateCellCultureQuery = $this->connection->createQueryBuilder()
            ->update("cell_culture")
            ->set("aliquot_id", ":ulid")
            ->where("id = :id")
        ;

        // Update cell cultures
        $affectedRows = 0;
        foreach ($this->cellCultures as $cellCulture) {
            $cellCultureId = $cellCulture["id"];
            $oldAliquotId = $cellCulture["aliquot_id"];

            // No need to update if aliquot is null!
            if ($oldAliquotId === null) {
                continue;
            }
            $newAliquotId = $idToUlid[$oldAliquotId];

            $affectedRows += $updateCellCultureQuery
                ->setParameter("id", $cellCultureId)
                ->setParameter("ulid", $newAliquotId)
                ->executeStatement()
            ;

            $this->logger->debug($updateCellCultureQuery->getSQL() . ", for id={$cellCultureId} and aliquot id {$oldAliquotId}=>{$newAliquotId}");
        }


        $this->logger->info(count($this->cellCultures) . " cell cultures found.");
        $this->logger->info("$affectedRows cell cultures updated.");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
