<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210825083138 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable("file");
        $table->addColumn("original_file_name", "string")
            ->setNotnull(true)
            ->setLength(255)
            ->setDefault("");
        $table->dropColumn("content");
        $table->getColumn("description")->setNotnull(false);

        $table = $schema->getTable("lot");
        $table->getColumn("bought_on")->setType(Type::getType("date"));
        $table->getColumn("opened_on")->setType(Type::getType("date"));
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("file");
        $table->dropColumn("original_file_name");
        $table->addColumn("content", "blob")->setNotnull(true)->setDefault("");

        $table = $schema->getTable("lot");
        $table->getColumn("bought_on")->setType(Type::getType("datetime"));
        $table->getColumn("opened_on")->setType(Type::getType("datetime"));
    }
}
