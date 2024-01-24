<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20240124075444 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a visualisation field to consumables.';
    }

    public function up(Schema $schema): void
    {
        $consumableTable = $schema->getTable("consumable");
        $consumableTable->addColumn("visualisation_id", Types::GUID)
            ->setNotnull(false)
            ->setComment("(DC2Type:ulid)");
        $consumableTable->addIndex(["visualisation_id"], "IDX_4475F0951A36181E");
        $consumableTable->addForeignKeyConstraint("file", ["visualisation_id"], ["id"], ["onDelete" => "SET NULL"], "FK_4475F0951A36181E");
    }

    public function down(Schema $schema): void
    {
        $consumableTable = $schema->getTable("consumable");
        $consumableTable->dropIndex("IDX_4475F0951A36181E");
        $consumableTable->removeForeignKey("FK_4475F0951A36181E");
        $consumableTable->dropColumn("visualisation_id");
    }
}
