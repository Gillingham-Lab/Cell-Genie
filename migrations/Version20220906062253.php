<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220906062253 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '[Cleanup] Removes left-over tables and relationships';
    }

    public function up(Schema $schema): void
    {
        // Remove old references from the removal of the antibody host table
        $table = $schema->getTable("antibody");
        $table->removeForeignKey("fk_5c97c6b1e10b57cb");
        $table->dropIndex("idx_5c97c6b1e10b57cb");
        $schema->dropTable("antibody_host");

        // Remove associations from the migration to epitopes
        $schema->getTable("antibody_protein")->removeForeignKey("fk_a4a178769926e711");
        $schema->dropTable("antibody_protein");

        // DB modifications caused by changes to ShortNameTrait
        $schema->getTable("recipe")->addUniqueIndex(["short_name"], "UNIQ_DA88B1373EE4B093");
        $schema->getTable("recipe")->getColumn("short_name")->setLength(50);
        $schema->getTable("epitope")->getColumn("short_name")->setLength(50);

        // From protein-migration to ulid from id
        $schema->getTable("protein")->dropIndex("uniq_98f8e1b2bf396750");

        // Other
        $schema->getTable("cell")->addUniqueIndex(["cell_number"], "UNIQ_CB8787E25AA36E0C");
    }

    public function down(Schema $schema): void
    {
        // We do not migrate back.
        $this->throwIrreversibleMigrationException("Irreversible migration");
    }
}
