<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210818072310 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable("antibody_host");
        $table->addColumn("id", "guid")
            ->setNotnull(true);
        $table->addColumn("name", "string")
            ->setNotnull(true)
            ->setLength(255)
            ->setComment("(DC2Type:ulid)");
        $table->setPrimaryKey(["id"]);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable("antibody_host");
    }
}
