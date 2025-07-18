<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20240404050249 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Part 2 of the migration of array-to-json conversion for cell proteins. This changes the type of the column and copies the values back.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("cell_protein");
        $table->addColumn("detection", Types::JSON)
            ->setNotnull(false)
            ->setComment("(DC2Type:json_document)")
            ->setPlatformOption("jsonb", true)
        ;
    }

    public function postUp(Schema $schema): void
    {
        // After the update, we copy the data from the temporary to the correct column
        $connection = $this->connection;
        $connection->executeStatement("UPDATE cell_protein SET detection = detection_b");
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("cell_protein");
        $table->dropColumn("detection");
    }
}
