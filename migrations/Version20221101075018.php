<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221101075018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds new ulid columns to Box and Rack and CellAliquot and Lot. Nullable for now.';
    }


    public function up(Schema $schema): void
    {
        $table = $schema->getTable("rack");
        $table->addColumn("ulid", "guid")->setNotnull(false)->setComment("(DC2Type:ulid)");

        $table = $schema->getTable("box");
        $table->addColumn("ulid", "guid")->setNotnull(false)->setComment("(DC2Type:ulid)");
        $table->addColumn("rack_ulid", "guid")->setNotnull(false)->setComment("(DC2Type:ulid)");

        $table = $schema->getTable("cell_aliquote");
        $table->addColumn("box_ulid", "guid")->setNotnull(false)->setComment("(DC2Type:ulid)");

        $table = $schema->getTable("lot");
        $table->addColumn("box_ulid", "guid")->setNotnull(false)->setComment("(DC2Type:ulid)");
    }

    public function down(Schema $schema): void
    {
        $schema->getTable("rack")->dropColumn("ulid");
        $schema->getTable("box")->dropColumn("ulid");
        $schema->getTable("box")->dropColumn("rack_ulid");
        $schema->getTable("cell_aliquote")->dropColumn("box_ulid");
        $schema->getTable("lot")->dropColumn("box_ulid");
    }
}
