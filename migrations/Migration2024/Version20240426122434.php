<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20240426122434 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a virtual ulid column to datum';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("new_experimental_datum");
        $this->addSql("ALTER TABLE new_experimental_datum ADD reference_uuid uuid GENERATED ALWAYS AS (CASE WHEN type = 'entityReference' OR type = 'uuid' THEN CAST(ENCODE(substring(value from 0 for 17), 'hex') AS uuid) ELSE null END) STORED");
        $this->addSql('COMMENT ON COLUMN new_experimental_datum.reference_uuid IS \'(DC2Type:ulid)\'');
    }

    public function down(Schema $schema): void
    {
        $schema->getTable("new_experimental_datum")
            ->dropColumn("reference_uuid");
    }
}
