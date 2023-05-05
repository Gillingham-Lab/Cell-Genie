<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220623075300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("cell");
        $table->getColumn("age")->setNotnull(false)->setDefault(null);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("cell");
        $table->getColumn("age")->setNotnull(true);
    }
}
