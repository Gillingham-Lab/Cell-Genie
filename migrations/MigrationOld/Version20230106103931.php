<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230106103931 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Adds an 'availability' column to lots, with default 'available'";
    }

    public function up(Schema $schema): void
    {
        $lotTable = $schema->getTable("lot");
        $lotTable->addColumn("availability", "string")->setLength(255)->setNotnull(true)->setDefault("available");
    }

    public function down(Schema $schema): void
    {
        $lotTable = $schema->getTable("lot");
        $lotTable->dropColumn("availability");
    }
}
