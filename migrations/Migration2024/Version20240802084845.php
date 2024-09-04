<?php

declare(strict_types=1);

namespace DoctrineMigrations2024;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240802084845 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Removes the cell culture flask table.';
    }

    public function up(Schema $schema): void
    {
        $experimentTable = $schema->getTable("experiment");
        $experimentTable->removeForeignKey("fk_136f58b2c14c34c2");
        $experimentTable->dropIndex("idx_136f58b2c14c34c2");
        $experimentTable->dropColumn("wellplate_id");

        $cultureFlaskTable = $schema->getTable("culture_flask");
        $cultureFlaskTable->removeForeignKey("fk_253a1758f603ee73");

        $schema->dropSequence("culture_flask_id_seq");
        $schema->dropSequence("cell_id_seq");
        $schema->dropSequence("cell_aliquote_id_seq");

        $schema->dropTable("culture_flask");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
