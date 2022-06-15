<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220614095451 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable("ext_log_entries");
        $table->addColumn("id", "integer")
            ->setNotnull(true)
            ->setAutoincrement(true);
        $table->addColumn("action", "string")
            ->setLength(8)
            ->setNotnull(true);
        $table->addColumn("logged_at", "datetime")
            ->setNotnull(true);
        $table->addColumn("object_id", "string")
            ->setLength(64)
            ->setNotnull(false)
            ->setDefault(null);
        $table->addColumn("object_class", "string")
            ->setLength(191)
            ->setNotnull(true);
        $table->addColumn("version", "integer")
            ->setNotnull(true);
        $table->addColumn("data", "text")
            ->setNotNull(false)
            ->setDefault(null)
            ->setComment("(DC2Type:array)");
        $table->addColumn("username", "string")
            ->setNotnull(false)
            ->setLength(191)
            ->setDefault(null);

        $table->addIndex(["object_class"], "log_class_lookup_idx");
        $table->addIndex(["logged_at"], "log_date_lookup_idx");
        $table->addIndex(["username"], "log_user_lookup_idx");
        $table->addIndex(["object_id", "object_class", "version"], "log_version_lookup_idx");
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable("ext_log_entries");
    }
}
