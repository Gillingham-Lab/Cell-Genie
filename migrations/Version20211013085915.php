<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211013085915 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("experimental_run");
        $table->addUniqueIndex(["experiment_id", "name"], indexName: "UNIQ_30B5493EFF444C85E237E06");
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("experimental_run");
        $table->dropIndex("UNIQ_30B5493EFF444C85E237E06");
    }
}
