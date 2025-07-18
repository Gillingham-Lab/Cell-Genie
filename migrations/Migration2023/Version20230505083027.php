<?php

declare(strict_types=1);

namespace DoctrineMigrations2023;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Uid\Ulid;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230505083027 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migrates database to the new cell group feature.';
    }

    private $upCellData = [];
    private $downCellData = [];

    /**
     * @throws SchemaException
     */
    private function addCommonColumns(Table $table)
    {
        $table->addColumn("morphology_id", "integer")->setNotnull(false)->setDefault(null);
        $table->addColumn("organism_id", "integer")->setNotnull(false)->setDefault(null);
        $table->addColumn("tissue_id", "integer")->setNotnull(false)->setDefault(null);

        $table->addColumn("cellosaurus_id", "string")->setLength(20)->setNotnull(false)->setDefault(null);
        $table->addColumn("age", "string")->setLength(255)->setNotnull(false)->setDefault(null);
        $table->addColumn("sex", "string")->setLength(50)->setNotnull(false)->setDefault(null);
        $table->addColumn("ethnicity", "string")->setLength(50)->setNotnull(false)->setDefault(null);
        $table->addColumn("disease", "string")->setLength(255)->setNotnull(false)->setDefault(null);
        $table->addColumn("rrid", "string")->setLength(255)->setNotnull(false)->setDefault(null);

        $table->addColumn("is_cancer", "boolean")->setNotnull(true)->setDefault(true);
        $table->addColumn("culture_type", "string")->setLength(255)->setNotnull(true)->setDefault("unknown");
    }

    /**
     * @throws SchemaException
     */
    private function dropCommonColumns(Table $table)
    {
        $table->dropColumn("morphology_id");
        $table->dropColumn("organism_id");
        $table->dropColumn("tissue_id");
        $table->dropColumn("cellosaurus_id");
        $table->dropColumn("age");
        $table->dropColumn("sex");
        $table->dropColumn("ethnicity");
        $table->dropColumn("disease");
        $table->dropColumn("rrid");
        $table->dropColumn("is_cancer");
        $table->dropColumn("culture_type");
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable("cell_group");

        $table->addColumn("id", "guid")->setNotnull(true)->setComment("(DC2Type:ulid)");
        $table->addColumn("name", "string")->setLength(255)->setNotnull(true);
        $table->addColumn("number", "string")->setLength(15)->setNotnull(true);
        $table->addColumn("parent_id", "guid")->setNotnull(false)->setComment("(DC2Type:ulid)");

        $this->addCommonColumns($table);

        $table->setPrimaryKey(["id"]);
        $table->addUniqueIndex(["name"], "UNIQ_52C689715E237E06");
        $table->addUniqueIndex(["number"], "UNIQ_52C6897196901F54");
        $table->addIndex(["morphology_id"], "IDX_52C68971B38B33AD");
        $table->addIndex(["organism_id"], "IDX_52C6897164180A36");
        $table->addIndex(["tissue_id"], "IDX_52C68971701EFC92");
        $table->addIndex(["parent_id"], "IDX_52C68971727ACA70");

        $table->addForeignKeyConstraint("morphology", ["morphology_id"], ["id"], ["onDelete" => "SET NULL"], "FK_52C68971B38B33AD");
        $table->addForeignKeyConstraint("organism", ["organism_id"], ["id"], ["onDelete" => "SET NULL"], "FK_52C6897164180A36");
        $table->addForeignKeyConstraint("tissue", ["tissue_id"], ["id"], ["onDelete" => "SET NULL"], "FK_52C68971701EFC92");
        $table->addForeignKeyConstraint("cell_group", ["parent_id"], ["id"], ["onDelete" => "SET NULL"], "FK_52C68971727ACA70");

        // Cell => CellGroup reference
        $table = $schema->getTable("cell");
        $table->addColumn("cell_group_id", "guid")->setNotnull(false)->setDefault(null)->setComment("(DC2Type:ulid)");
        $table->addIndex(["cell_group_id"], "IDX_CB8787E2AB348382");
        $table->addForeignKeyConstraint("cell_group", ["cell_group_id"], ["id"], ["onDelete" => "SET NULL"], "FK_CB8787E2AB348382");

        $table->dropIndex("IDX_CB8787E2B38B33AD");
        $table->dropIndex("IDX_CB8787E2701EFC92");
        $table->dropIndex("IDX_CB8787E264180A36");
        $table->removeForeignKey("FK_CB8787E2B38B33AD");
        $table->removeForeignKey("FK_CB8787E2701EFC92");
        $table->removeForeignKey("FK_CB8787E264180A36");

        $this->dropCommonColumns($table);
    }

    public function preUp(Schema $schema): void
    {
        // Get all cells
        // Create new ulids

        $allCellsQuery = $this->connection->createQueryBuilder()
            ->select("c.*")
            ->from("cell", "c")
            ->orderBy("c.parent_id", "ASC NULLS FIRST");

        $result = $allCellsQuery->fetchAllAssociative();

        $newCellGroups = [];
        $cellsToCellGroup = [];
        $cells = [];

        foreach ($result as $cell) {
            if ($cell["cellosaurus_id"]) {
                $cellGroupNumber = $cell["cellosaurus_id"];
            } elseif ($cell["rrid"]) {
                $cellGroupNumber = $cell["rrid"];
            } else {
                if ($cell["parent_id"] and isset($cells[$cell["parent_id"]])) {
                    $cellGroupNumber = $cells[$cell["parent_id"]][0];
                } else {
                    $cellGroupNumber = $cell["cell_number"];
                }
            }

            if (!isset($newCellGroups[$cellGroupNumber])) {
                $newCellGroups[$cellGroupNumber] = [new Ulid(), $cell];
            }

            $cellsToCellGroup[$cell["id"]] = &$newCellGroups[$cellGroupNumber];
            $cells[$cell["id"]] = [$cellGroupNumber, $cell];
        }

        $this->upCellData = [
            "newCellGroups" => $newCellGroups,
            "cellsToCellGroup" => $cellsToCellGroup,
            "cells" => $cells,
        ];
    }

    public function postUp(Schema $schema): void
    {
        [
            "newCellGroups" => $newCellGroups,
            "cellsToCellGroup" => $cellsToCellGroup,
            "cells" => $cells,
        ] = $this->upCellData;

        /**
         * @var string $cellGroupNumber
         * @var Ulid $ulid
         * @var array $cellGroupData
         */
        foreach ($newCellGroups as $cellGroupNumber => [$ulid, $cellGroupData]) {
            // Create new cell groups
            $insertQuery = $this->connection->createQueryBuilder();
            $insertQuery = $insertQuery
                ->insert("cell_group")
                ->setValue("id", ":ulid")
                ->setValue("morphology_id", ":morphology_id")
                ->setValue("organism_id", ":organism_id")
                ->setValue("tissue_id", ":tissue_id")
                ->setValue("cellosaurus_id", ":cellosaurus_id")
                ->setValue("age", ":age")
                ->setValue("sex", ":sex")
                ->setValue("ethnicity", ":ethnicity")
                ->setValue("disease", ":disease")
                ->setValue("rrid", ":rrid")
                ->setValue("is_cancer", ":is_cancer")
                ->setValue("culture_type", ":culture_type")
                ->setValue("name", ":name")
                ->setValue("number", ":number")
                ->setParameters([
                    "ulid" => $ulid->toRfc4122(),
                    "morphology_id" => $cellGroupData["morphology_id"],
                    "organism_id" => $cellGroupData["organism_id"],
                    "tissue_id" => $cellGroupData["tissue_id"],
                    "cellosaurus_id" => $cellGroupData["cellosaurus_id"],
                    "age" => $cellGroupData["age"],
                    "sex" => $cellGroupData["sex"],
                    "ethnicity" => $cellGroupData["ethnicity"],
                    "disease" => $cellGroupData["disease"],
                    "rrid" => $cellGroupData["rrid"],
                    "is_cancer" => $cellGroupData["is_cancer"] ? "1" : "0",
                    "culture_type" => $cellGroupData["culture_type"],
                    "name" => $cellGroupData["name"],
                    "number" => $cellGroupNumber,
                ], [
                    "guid",
                    "integer",
                    "integer",
                    "integer",
                    "integer",
                    "string",
                    "string",
                    "string",
                    "string",
                    "string",
                    "boolean",
                    "string",
                    "string",
                    "string",
                ]);

            $this->write(sprintf("Creating a new CellGroup (number=%s, id=%s, from %s)", $cellGroupNumber, $ulid->toRfc4122(), $cellGroupData["id"]));
            $insertQuery->executeQuery();
        }

        foreach ($cells as $cellId => [$cellGroupNumber, $cell]) {
            // Update cell entry
            $ulid = $cellsToCellGroup[$cellId][0];

            $updateQuery = $this->connection->createQueryBuilder()
                ->update("cell")
                ->set("cell_group_id", ":cell_group_id")
                ->where("id = :id")
                ->setParameter("cell_group_id", $ulid->toRfc4122())
                ->setParameter("id", $cellId);

            $this->write(sprintf("Setting cell group of cell %s (id=%s) to %s (id=%s)", $cell["cell_number"], $cellId, $cellGroupNumber, $ulid->toRfc4122()));

            $updateQuery->executeQuery();
        }
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("cell");
        $table->removeForeignKey("FK_CB8787E2AB348382");
        $table->dropIndex("IDX_CB8787E2AB348382");
        $table->dropColumn("cell_group_id");
        $this->addCommonColumns($table);

        $table->addIndex(["morphology_id"], "IDX_CB8787E2B38B33AD");
        $table->addIndex(["tissue_id"], "IDX_CB8787E2701EFC92");
        $table->addIndex(["organism_id"], "IDX_CB8787E264180A36");
        $table->addForeignKeyConstraint("morphology", ["morphology_id"], ["id"], ["onDelete" => "SET NULL"], "FK_CB8787E2B38B33AD");
        $table->addForeignKeyConstraint("tissue", ["tissue_id"], ["id"], ["onDelete" => "SET NULL"], "FK_CB8787E2701EFC92");
        $table->addForeignKeyConstraint("organism", ["organism_id"], ["id"], ["onDelete" => "SET NULL"], "FK_CB8787E264180A36");

        $table = $schema->getTable("cell_group");
        $table->removeForeignKey("FK_52C68971B38B33AD");
        $table->removeForeignKey("FK_52C6897164180A36");
        $table->removeForeignKey("FK_52C68971701EFC92");
        $table->removeForeignKey("FK_52C68971727ACA70");
        $schema->dropTable("cell_group");

    }

    public function preDown(Schema $schema): void
    {
        $allCellsQuery = $this->connection->createQueryBuilder()
            ->select("c.id, c.*")
            ->from("cell", "c");

        $allCells = $allCellsQuery->fetchAllAssociativeIndexed();

        $allCellGroupsQuery = $this->connection->createQueryBuilder()
            ->select("c.id, c.*")
            ->from("cell_group", "c");

        $allCellGroups = $allCellGroupsQuery->fetchAllAssociativeIndexed();

        $this->downCellData = [
            "allCells" => $allCells,
            "allCellGroups" => $allCellGroups,
        ];
    }

    public function postDown(Schema $schema): void
    {
        // Now we should complete the data again that each cell is missing
        [
            "allCells" => $allCells,
            "allCellGroups" => $allCellGroups,
        ] = $this->downCellData;

        foreach ($allCells as $id => $cell) {
            if (empty($allCellGroups[$cell["cell_group_id"]])) {
                // Backgrade not possible
                $this->write(sprintf("Cell %s (id=%s) had empty cell culture id.", $cell["cell_number"], $id));
                continue;
            }
            $cellGroupData = $allCellGroups[$cell["cell_group_id"]];

            $updateQuery = $this->connection->createQueryBuilder()
                ->update("cell")
                ->set("morphology_id", ":morphology_id")
                ->set("organism_id", ":organism_id")
                ->set("tissue_id", ":tissue_id")
                ->set("cellosaurus_id", ":cellosaurus_id")
                ->set("age", ":age")
                ->set("sex", ":sex")
                ->set("ethnicity", ":ethnicity")
                ->set("disease", ":disease")
                ->set("rrid", ":rrid")
                ->set("is_cancer", ":is_cancer")
                ->set("culture_type", ":culture_type")
                ->where("id = :id")
                ->setParameters([
                    "id" => $id,
                    "morphology_id" => $cellGroupData["morphology_id"],
                    "organism_id" => $cellGroupData["organism_id"],
                    "tissue_id" => $cellGroupData["tissue_id"],
                    "cellosaurus_id" => $cellGroupData["cellosaurus_id"],
                    "age" => $cellGroupData["age"],
                    "sex" => $cellGroupData["sex"],
                    "ethnicity" => $cellGroupData["ethnicity"],
                    "disease" => $cellGroupData["disease"],
                    "rrid" => $cellGroupData["rrid"],
                    "is_cancer" => $cellGroupData["is_cancer"] ? "1" : "0",
                    "culture_type" => $cellGroupData["culture_type"],
                ]);

            $this->write(sprintf("Writing data to cell %s (id=%s) from former cell group %s (id=%s)", $cell["cell_number"], $id, $cellGroupData["number"], $cell["cell_group_id"]));

            $updateQuery->executeQuery();
        }
    }
}
