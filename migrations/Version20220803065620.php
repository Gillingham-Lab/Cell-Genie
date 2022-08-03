<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220803065620 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $schema->getTable("experimental_condition")
            ->addColumn("is_x", "boolean")->setNotnull(false);
        $schema->getTable("experimental_measurement")
            ->addColumn("is_y", "boolean")->setNotnull(false);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
