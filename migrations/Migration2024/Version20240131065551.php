<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20240131065551 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a field to cells to allow to configure whether a cell culture should be created upon aliquot consumption or not.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("cell");
        $table->addColumn("aliquot_consumption_creates_culture", Types::BOOLEAN)
            ->setNotnull(true)
            ->setDefault(true);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("cell");
        $table->dropColumn("aliquot_consumption_creates_culture");
    }
}
