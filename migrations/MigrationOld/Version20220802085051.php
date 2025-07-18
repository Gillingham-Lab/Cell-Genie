<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220802085051 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Make antibody.id nullable, and antibody.ulid not.
        $table = $schema->getTable("antibody");
        $table->getColumn("id")->setNotnull(false);
        $table->getColumn("ulid")->setNotnull(true);

        // Set primary key to ulid
        $table->setPrimaryKey(["ulid"]);

        // Update antibody-lots to have correct nullable columns and new primary key and foreign key contraints
        $table = $schema->getTable("antibody_lots");
        $table->dropColumn("antibody_id");
        $table->getColumn("antibody_ulid")->setNotnull(true);

        $table->setPrimaryKey(["antibody_ulid", "lot_id"]);
        $table->addForeignKeyConstraint("antibody", ["antibody_ulid"], ["ulid"], [], "FK_5C96DB86F7E9411");
        $table->addIndex(["antibody_ulid"], "IDX_5C96DB86F7E9411");
        $table->dropIndex("idx_5c96db8651162764");

        // Update antibody-vendor-documentation-files similarly
        $table = $schema->getTable("antibody_vendor_documentation_files");
        $table->dropColumn("antibody_id");
        $table->getColumn("antibody_ulid")->setNotnull(true);

        $table->setPrimaryKey(["antibody_ulid", "file_id"]);
        $table->addForeignKeyConstraint("antibody", ["antibody_ulid"], ["ulid"], [], "FK_5B63125DF7E9411");
        $table->addIndex(["antibody_ulid"], "IDX_5B63125DF7E9411");
        $table->dropIndex("idx_5b63125d51162764");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
