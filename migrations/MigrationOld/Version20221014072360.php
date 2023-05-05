<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221014072360 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a a maxVial to cell aliquot table.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("cell_aliquote");
        $table->addColumn("max_vials", "integer")
            ->setNotnull(false);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("cell_aliquote");
        $table->dropColumn("max_vials");
    }
}
