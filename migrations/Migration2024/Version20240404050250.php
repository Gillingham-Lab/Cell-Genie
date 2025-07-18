<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20240404050250 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Part 3 of the migration of array-to-json conversion for cell proteins. This removes the temporary column.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("cell_protein");
        $table->dropColumn("detection_b");
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("cell_protein");
        $table->addColumn("detection_b", Types::JSON)
            ->setNotnull(false)
            ->setComment("(DC2Type:json_document)")
            ->setPlatformOption("jsonb", true)
        ;
    }

    public function postDown(Schema $schema): void
    {
        $connection = $this->connection;
        $connection->executeStatement("UPDATE cell_protein SET detection_b = detection");
    }
}
