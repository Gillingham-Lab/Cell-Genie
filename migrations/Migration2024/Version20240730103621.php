<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240730103621 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Step 2: Cell aliquot migration to use UUID instead of SEQUENCE';
    }

    public function up(Schema $schema): void
    {
        // Drop old table
        $schema->dropTable("cell_aliquote");

        // Remake foreign keys
        $cellCulture = $schema->getTable("cell_culture");
        $cellCulture->addIndex(["aliquot_id"], "IDX_D3D5765CDF934280");
        $cellCulture->addForeignKeyConstraint("cell_aliquot", ["aliquot_id"], ["id"], ["onDelete" => "CASCADE"], "FK_D3D5765CDF934280");
    }

    public function down(Schema $schema): void
    {
        // Don't migrate back
        $this->throwIrreversibleMigrationException();
    }
}
