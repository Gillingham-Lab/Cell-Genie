<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20240426122433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds two condition reference columns to datasets';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("new_experimental_run_data_set");
        $table->addColumn("condition_id", Types::GUID)->setNotnull(true)->setComment("(DC2Type:ulid)");
        $table->addColumn("control_condition_id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");

        $table->addIndex(["condition_id"], "IDX_7A124F0F887793B6");
        $table->addIndex(["control_condition_id"], "IDX_7A124F0F7B31A050");

        $table->addForeignKeyConstraint("new_experimental_run_condition", ["condition_id"], ["id"], ["onDelete" => "CASCADE"], "FK_7A124F0F887793B6");
        $table->addForeignKeyConstraint("new_experimental_run_condition", ["control_condition_id"], ["id"], ["onDelete" => "CASCADE"], "FK_7A124F0F7B31A050");
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("new_experimental_run_data_set");
        $table->removeForeignKey("FK_7A124F0F887793B6");
        $table->removeForeignKey("FK_7A124F0F7B31A050");
        $table->dropColumn("condition_id");
        $table->dropColumn("control_condition_id");
    }
}
