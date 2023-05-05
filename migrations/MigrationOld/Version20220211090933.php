<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220211090933 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable("chemical");
        $table->getColumn("short_name")->setLength(20);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("chemical");
        $table->getColumn("short_name")->setLength(10);
    }
}
