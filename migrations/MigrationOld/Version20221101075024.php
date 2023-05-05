<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221101075024 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds box coordinate to cell aliquot.';
    }


    public function up(Schema $schema): void
    {
        $table = $schema->getTable("cell_aliquote");
        $table->addColumn("box_coordinate", "string")->setNotnull(false)->setLength(10);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("cell_aliquote");
        $table->dropColumn("box_coordinate");
    }
}
