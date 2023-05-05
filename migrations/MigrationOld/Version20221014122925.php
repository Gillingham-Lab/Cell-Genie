<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221014122925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds attachments for substance and lots.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable("lot_file");
        $table->addColumn("lot_id", "guid")
            ->setComment("(DC2Type:ulid)")
            ->setNotnull(true);
        $table->addColumn("file_id", "guid")
            ->setComment("(DC2Type:ulid)")
            ->setNotnull(true);

        $table->setPrimaryKey(["lot_id", "file_id"]);
        $table->addIndex(["lot_id"], "IDX_18B3A850A8CBA5F7");
        $table->addUniqueIndex(["file_id"], "UNIQ_18B3A85093CB796C");

        $table->addForeignKeyConstraint("lot", ["lot_id"], ["id"], ["onDelete" => "CASCADE"], "FK_18B3A850A8CBA5F7");
        $table->addForeignKeyConstraint("file", ["file_id"], ["id"], [], "FK_18B3A850A8CBA5F7");

        $table = $schema->createTable("substance_file");
        $table->addColumn("substance_ulid", "guid")
            ->setComment("(DC2Type:ulid)")
            ->setNotnull(true);
        $table->addColumn("file_id", "guid")
            ->setComment("(DC2Type:ulid)")
            ->setNotnull(true);

        $table->setPrimaryKey(["substance_ulid", "file_id"]);
        $table->addIndex(["substance_ulid"], "IDX_C7E39DA0232D562B");
        $table->addUniqueIndex(["file_id"], "UNIQ_C7E39DA093CB796C");

        $table->addForeignKeyConstraint("substance", ["substance_ulid"], ["ulid"], ["onDelete" => "CASCADE"], "FK_C7E39DA0232D562B");
        $table->addForeignKeyConstraint("file", ["file_id"], ["id"], [], "FK_C7E39DA093CB796C");
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable("lot_file");
        $schema->dropTable("substance_file");
    }
}
