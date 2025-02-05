<?php
declare(strict_types=1);

namespace DoctrineMigrations2025;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250203101439 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds an experimental model table and a reference table to add models to experimental designs.';
    }

    public function up(Schema $schema): void
    {
        $modelTable = $schema->createTable("experimental_model");
        $modelTable->addColumn("id", Types::GUID, ["notnull" => true, "comment" => "(DC2Type:ulid)"]);
        $modelTable->addColumn("parent_id", Types::GUID, ["notnull" => false, "comment" => "(DC2Type:ulid)"]);
        $modelTable->addColumn("name", Types::STRING, ["notnull" => true, "length" => 255]);
        $modelTable->addColumn("model", Types::STRING, ["notnull" => true, "length" => 255]);
        $modelTable->addColumn("configuration", Types::JSON, ["notnull" => false]);
        $modelTable->addColumn("result", Types::JSON, ["notnull" => false]);
        $modelTable->setPrimaryKey(["id"]);
        $modelTable->addIndex(["parent_id"], "IDX_831140EC727ACA70");
        $modelTable->addForeignKeyConstraint("experimental_model", ["parent_id"], ["id"], ["onDelete" => "CASCADE"], "FK_831140EC727ACA70");

        $designModelTable = $schema->createTable("new_experimental_design_models");
        $designModelTable->addColumn("design_id", Types::GUID, ["notnull" => true, "comment" => "(DC2Type:ulid)"]);
        $designModelTable->addColumn("model_id", Types::GUID, ["notnull" => true, "comment" => "(DC2Type:ulid)"]);
        $designModelTable->setPrimaryKey(["design_id", "model_id"]);
        $designModelTable->addUniqueIndex(["model_id"], "UNIQ_279FE3107975B7E7");

        $designModelTable->addForeignKeyConstraint("new_experimental_design", ["design_id"], ["id"], ["onDelete" => "CASCADE"], "FK_279FE310E41DC9B2");
        $designModelTable->addForeignKeyConstraint("experimental_model", ["model_id"], ["id"], ["onDelete" => "CASCADE"], "FK_279FE3107975B7E7");

        $conditionModelTable = $schema->createTable("new_experimental_run_condition_model");
        $conditionModelTable->addColumn("condition_id", Types::GUID, ["notnull" => true, "comment" => "(DC2Type:ulid)"]);
        $conditionModelTable->addColumn("model_id", Types::GUID, ["notnull" => true, "comment" => "(DC2Type:ulid)"]);
        $conditionModelTable->setPrimaryKey(["condition_id", "model_id"]);
        $conditionModelTable->addIndex(["condition_id"], "IDX_1FE93253887793B6");
        $conditionModelTable->addUniqueIndex(["model_id"], "UNIQ_1FE932537975B7E7");

        $conditionModelTable->addForeignKeyConstraint("new_experimental_run_condition", ["condition_id"], ["id"], ["onDelete" => "CASCADE"], "FK_1FE93253887793B6");
        $conditionModelTable->addForeignKeyConstraint("experimental_model", ["model_id"], ["id"], ["onDelete" => "CASCADE"], "FK_1FE932537975B7E7");
    }

    public function down(Schema $schema): void
    {
        $designModelTable = $schema->getTable("new_experimental_design_models");
        $designModelTable->removeForeignKey("FK_279FE310E41DC9B2");
        $designModelTable->removeForeignKey("FK_279FE3107975B7E7");

        $conditionModelTable = $schema->getTable("new_experimental_run_condition_model");
        $conditionModelTable->removeForeignKey("FK_1FE93253887793B6");
        $conditionModelTable->removeForeignKey("FK_1FE932537975B7E7");

        $modelTable = $schema->getTable("experimental_model");
        $modelTable->removeForeignKey("FK_831140EC727ACA70");

        $schema->dropTable("experimental_model");
        $schema->dropTable("new_experimental_design_models");
    }
}
