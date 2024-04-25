<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use App\Doctrine\Migration\AddIdColumnTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20240422135532 extends AbstractMigration
{
    use AddIdColumnTrait;

    public function getDescription(): string
    {
        return 'Adds the rest of the new experimental run tables';
    }

    public function up(Schema $schema): void
    {
        $experimentalRun = $schema->createTable("new_experimental_run");
        $this->addIdColumn($experimentalRun);
        $experimentalRun->addColumn("scientist_id", Types::GUID)
            ->setComment("(DC2Type:ulid)")
            ->setNotnull(true);
        $experimentalRun->addColumn("name", Types::STRING)
            ->setLength(255)
            ->setNotnull(true);
        $experimentalRun->addColumn("created_at", Types::DATETIME_IMMUTABLE)->setNotnull(false)->setComment("(DC2Type:datetime_immutable)");
        $experimentalRun->addColumn("modified_at", Types::DATETIME_MUTABLE)->setNotnull(false);
        $experimentalRun->addColumn("labjournal", Types::TEXT)->setNotnull(false);
        $experimentalRun->addColumn("comment", Types::TEXT)->setNotnull(false);

        $experimentalRun->addIndex(["scientist_id"], "IDX_14F2646AEBA327D6");

        $experimentalRunDatum = $schema->createTable("new_experimental_run_datum");
        $experimentalRunDatum->addColumn("experiment_id", Types::GUID)
            ->setComment("(DC2Type:ulid)")
            ->setNotnull(false);
        $experimentalRunDatum->addColumn("datum_id", Types::GUID)
            ->setComment("(DC2Type:ulid)")
            ->setNotnull(false);
        $experimentalRunDatum->setPrimaryKey(["experiment_id", "datum_id"]);
        $experimentalRunDatum->addIndex(["experiment_id"], "IDX_631C2281FF444C8");
        $experimentalRunDatum->addIndex(["datum_id"], "IDX_631C2281ED8F25ED");


        $experimentalRunCondition = $schema->createTable("new_experimental_run_condition");
        $this->addIdColumn($experimentalRunCondition);
        $experimentalRunCondition->addColumn("experimental_run_id", Types::GUID)
            ->setComment("(DC2Type:ulid)")
            ->setNotnull(true);
        $experimentalRunCondition->addColumn("name", Types::STRING)
            ->setLength(255)
            ->setNotnull(true);
        $experimentalRunCondition->addColumn("control", Types::BOOLEAN)->setNotnull(true);
        $experimentalRunCondition->addIndex(["experimental_run_id"], "IDX_19DFC6A5D204BED5");

        $experimentalRunConditionDatum = $schema->createTable("new_experimental_run_condition_datum");
        $experimentalRunConditionDatum->addColumn("condition_id", Types::GUID)
            ->setComment("(DC2Type:ulid)")
            ->setNotnull(false);
        $experimentalRunConditionDatum->addColumn("datum_id", Types::GUID)
            ->setComment("(DC2Type:ulid)")
            ->setNotnull(false);
        $experimentalRunConditionDatum->setPrimaryKey(["condition_id", "datum_id"]);
        $experimentalRunConditionDatum->addIndex(["condition_id"], "IDX_D3C5A6A6887793B6");
        $experimentalRunConditionDatum->addIndex(["datum_id"], "IDX_D3C5A6A6ED8F25ED");

        $experimentalRunDataSet = $schema->createTable("new_experimental_run_data_set");
        $this->addIdColumn($experimentalRunDataSet);
        $experimentalRunDataSet->addColumn("experiment_id", Types::GUID)
            ->setComment("(DC2Type:ulid)")
            ->setNotnull(true);
        $experimentalRunDataSet->addIndex(["experiment_id"], "IDX_7A124F0FFF444C8");

        $experimentalRunDataSetDatum = $schema->createTable("new_experimental_run_data_set_datum");
        $experimentalRunDataSetDatum->addColumn("data_set_id", Types::GUID)
            ->setComment("(DC2Type:ulid)")
            ->setNotnull(false);
        $experimentalRunDataSetDatum->addColumn("datum_id", Types::GUID)
            ->setComment("(DC2Type:ulid)")
            ->setNotnull(false);
        $experimentalRunDataSetDatum->setPrimaryKey(["data_set_id", "datum_id"]);
        $experimentalRunDataSetDatum->addIndex(["data_set_id"], "IDX_1902056F70053C01");
        $experimentalRunDataSetDatum->addIndex(["datum_id"], "IDX_1902056FED8F25ED");

        $experimentalRun->addForeignKeyConstraint("user_accounts", ["scientist_id"], ["id"], [], "FK_14F2646AEBA327D6");
        $experimentalRunDatum->addForeignKeyConstraint("new_experimental_run", ["experiment_id"], ["id"], ["onDelete" => "CASCADE"], "FK_631C2281FF444C8");
        $experimentalRunDatum->addForeignKeyConstraint("new_experimental_datum", ["datum_id"], ["id"], ["onDelete" => "CASCADE"], "FK_631C2281ED8F25ED");
        $experimentalRunCondition->addForeignKeyConstraint("new_experimental_run", ["experimental_run_id"], ["id"], [], "FK_19DFC6A5D204BED5");
        $experimentalRunConditionDatum->addForeignKeyConstraint("new_experimental_run_condition", ["condition_id"], ["id"], ["onDelete" => "CASCADE"], "FK_D3C5A6A6887793B6");
        $experimentalRunConditionDatum->addForeignKeyConstraint("new_experimental_datum", ["datum_id"], ["id"], ["onDelete" => "CASCADE"], "FK_D3C5A6A6ED8F25ED");
        $experimentalRunDataSet->addForeignKeyConstraint("new_experimental_run", ["experiment_id"], ["id"], [], "FK_7A124F0FFF444C8");
        $experimentalRunDataSetDatum->addForeignKeyConstraint("new_experimental_run_data_set", ["data_set_id"], ["id"], ["onDelete" => "CASCADE"], "FK_1902056F70053C01");
        $experimentalRunDataSetDatum->addForeignKeyConstraint("new_experimental_datum", ["datum_id"], ["id"], ["onDelete" => "CASCADE"], "FK_1902056FED8F25ED");
    }

    public function down(Schema $schema): void
    {
        $schema->getTable("new_experimental_run")->removeForeignKey("FK_14F2646AEBA327D6");
        $schema->getTable("new_experimental_run_datum")->removeForeignKey("FK_631C2281FF444C8");
        $schema->getTable("new_experimental_run_datum")->removeForeignKey("FK_631C2281ED8F25ED");
        $schema->getTable("new_experimental_run_condition")->removeForeignKey("FK_19DFC6A5D204BED5");
        $schema->getTable("new_experimental_run_condition_datum")->removeForeignKey("FK_D3C5A6A6887793B6");
        $schema->getTable("new_experimental_run_condition_datum")->removeForeignKey("FK_D3C5A6A6ED8F25ED");
        $schema->getTable("new_experimental_run_data_set")->removeForeignKey("FK_7A124F0FFF444C8");
        $schema->getTable("new_experimental_run_data_set_datum")->removeForeignKey("FK_1902056F70053C01");
        $schema->getTable("new_experimental_run_data_set_datum")->removeForeignKey("FK_1902056FED8F25ED");

        $schema->dropTable("new_experimental_run");
        $schema->dropTable("new_experimental_run_datum");
        $schema->dropTable("new_experimental_run_condition");
        $schema->dropTable("new_experimental_run_condition_datum");
        $schema->dropTable("new_experimental_run_data_set");
        $schema->dropTable("new_experimental_run_data_set_datum");
    }
}
