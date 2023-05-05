<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221101075022 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Data migration: Add foreign key constraints back.';
    }

    public function up(Schema $schema): void
    {
        $schema->getTable("box")->addForeignKeyConstraint("rack", ["rack_ulid"], ["ulid"], ["onDelete" => "SET NULL"], "FK_8A9483AA5FFB966");
        $schema->getTable("box")->addIndex(["rack_ulid"], "IDX_8A9483AA5FFB966");

        $schema->getTable("lot")->addForeignKeyConstraint("box", ["box_ulid"], ["ulid"], ["onDelete" => "SET NULL"], "FK_B81291B34EC8450");
        $schema->getTable("lot")->addIndex(["box_ulid"], "IDX_B81291B34EC8450");

        $schema->getTable("cell_aliquote")->addForeignKeyConstraint("box", ["box_ulid"], ["ulid"], ["onDelete" => "SET NULL"], "FK_E2BD616334EC8450");
        $schema->getTable("cell_aliquote")->addIndex(["box_ulid"], "IDX_E2BD616334EC8450");

        $schema->getTable("rack")->dropColumn("id");
        $schema->getTable("box")->dropColumn("id");
        $schema->getTable("box")->dropColumn("rack_id");
        $schema->getTable("lot")->dropColumn("box_id");
        $schema->getTable("cell_aliquote")->dropColumn("box_id");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
