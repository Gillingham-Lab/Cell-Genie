<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220602055117 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("recipe");
        $table->addColumn("comment", "text")
            ->setNotnull(false);
        $table->addColumn("pH", "float")
            ->setNotnull(false);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("recipe");
        $table->dropColumn("comment");
        $table->dropColumn("pH");
    }
}
