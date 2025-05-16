<?php
declare(strict_types=1);

namespace DoctrineMigrations2025;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250512100735 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a visualisation field to the instrument entity.';
    }

    public function up(Schema $schema): void
    {
        $instrumentTable = $schema->getTable("instrument");
        $instrumentTable->addColumn("visualisation_id", "guid")->setComment("(DC2Type:ulid)")->setNotnull(false);
        $instrumentTable->addIndex(["visualisation_id"], "IDX_3CBF69DD1A36181E");
        $instrumentTable->addForeignKeyConstraint("file", ["visualisation_id"], ["id"], ["onDelete" => "SET NULL", "onUpdate" => "CASCADE"], "FK_3CBF69DD1A36181E");
    }

    public function down(Schema $schema): void
    {
        $instrumentTable = $schema->getTable("instrument");
        $instrumentTable->dropIndex("IDX_3CBF69DD1A36181E");
        $instrumentTable->removeForeignKey("FK_3CBF69DD1A36181E");
        $instrumentTable->dropColumn("visualisation_id");
    }
}
