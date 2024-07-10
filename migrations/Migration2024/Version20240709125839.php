<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20240709125839 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a variable role field to experimental design fields and makes the design of experimental runs not-nullable';
    }

    public function up(Schema $schema): void
    {
        $designFieldTable = $schema->getTable("new_experimental_design_field");
        $designFieldTable->addColumn("variable_role", Types::STRING)->setLength(255)->setNotnull(false);

        $runTable = $schema->getTable("new_experimental_run");
        $runTable->getColumn("design_id")->setNotnull(true);
    }

    public function down(Schema $schema): void
    {
        $designFieldTable = $schema->getTable("new_experimental_design_field");
        $designFieldTable->dropColumn("variable_role");

        $runTable = $schema->getTable("new_experimental_run");
        $runTable->getColumn("design_id")->setNotnull(false);
    }
}
