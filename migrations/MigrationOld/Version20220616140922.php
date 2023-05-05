<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220616140922 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable("vocabulary");
        $table->addColumn("id", "guid")->setComment("(DC2Type:ulid)");
        $table->addColumn("name", "string")->setLength(255);
        $table->addColumn("vocabulary", "text")->setDefault('a:0:{}')->setComment("(DC2Type:array)");

        $table->addUniqueIndex(["name"], "UNIQ_9099C97B5E237E06");
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("vocabulary");
        $table->removeUniqueConstraint("UNIQ_9099C97B5E237E06");
        $schema->dropTable("vocabulary");
    }
}
