<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210824072842 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable("antibody_vendor_documentation_files");
        $table->addColumn("antibody_id", "integer")
            ->setNotnull(true)
        ;
        $table->addColumn("file_id", "guid")
            ->setNotnull(true)
            ->setComment("(DC2Type:ulid)")
        ;
        $table->setPrimaryKey(["antibody_id", "file_id"]);
        $table->addIndex(["antibody_id"], indexName: "IDX_5B63125D51162764");
        $table->addUniqueIndex(["file_id"], indexName: "UNIQ_5B63125D93CB796C");

        $table->addForeignKeyConstraint("antibody", ["antibody_id"], ["id"], name: "FK_5B63125D51162764");
        $table->addForeignKeyConstraint("file", ["file_id"], ["id"], name: "FK_5B63125D93CB796C");
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable("antibody_vendor_documentation_files");
    }
}
