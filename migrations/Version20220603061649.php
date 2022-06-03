<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220603061649 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("cell");
        $table->addColumn("rrid", "string")
            ->setNotnull(false)
            ->setLength(255);

        $table->addColumn("cellosaurus_id", "string")
            ->setNotnull(false)
            ->setLength(20);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("cell");
        $table->dropColumn("rrid");
        $table->dropColumn("cellosaurus_id");
    }
}
