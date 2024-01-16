<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20240116064358 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a relation table between consumables and instruments';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable("instrument_consumable");
        $table->addColumn("instrument_id", Types::GUID)
            ->setNotnull(true)
            ->setComment("(DC2Type:ulid)");
        $table->addColumn("consumable_id", Types::GUID)
            ->setNotnull(true)
            ->setComment("(DC2Type:ulid)");
        $table->setPrimaryKey(["instrument_id", "consumable_id"]);
        $table->addIndex(["instrument_id"], "IDX_8BB8C395CF11D9C");
        $table->addIndex(["consumable_id"], "IDX_8BB8C395A94ADB61");
        $table->addForeignKeyConstraint("instrument", ["instrument_id"], ["id"], ["onDelete" => "CASCADE"], "FK_8BB8C395CF11D9C");
        $table->addForeignKeyConstraint("consumable", ["consumable_id"], ["id"], ["onDelete" => "CASCADE"], "FK_8BB8C395A94ADB61");
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("instrument_consumable");
        $table->removeForeignKey("FK_8BB8C395CF11D9C");
        $table->removeForeignKey("FK_8BB8C395A94ADB61");

        $schema->dropTable("instrument_consumable");
    }
}
