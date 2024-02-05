<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20240205095609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a simple resource table';
    }

    public function up(Schema $schema): void
    {
        $resourceTable = $schema->createTable("resource");
        $resourceTable->addColumn("id", Types::GUID)
            ->setNotnull(true)
            ->setComment("(DC2Type:ulid)");
        $resourceTable->addColumn("visualisation_id", Types::GUID)
            ->setNotnull(false)
            ->setComment("(DC2Type:ulid)");
        $resourceTable->addColumn("category", Types::STRING)
            ->setNotnull(true)
            ->setLength(255);
        $resourceTable->addColumn("url", Types::STRING)
            ->setNotnull(true)
            ->setLength(255);
        $resourceTable->addColumn("comment", Types::TEXT)
            ->setNotnull(false);
        $resourceTable->addColumn("long_name", Types::STRING)
            ->setNotnull(true)
            ->setLength(255);
        $resourceTable->addColumn("owner_id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");
        $resourceTable->addColumn("group_id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");
        $resourceTable->addColumn("privacy_level", Types::SMALLINT)->setNotnull(true)->setDefault(2);

        $resourceTable->addIndex(["visualisation_id"], "IDX_BC91F4161A36181E");
        $resourceTable->addIndex(["owner_id"], "IDX_BC91F4167E3C61F9");
        $resourceTable->addIndex(["group_id"], "IDX_BC91F416FE54D947");
        $resourceTable->setPrimaryKey(["id"]);
        $resourceTable->addForeignKeyConstraint("file", ["visualisation_id"], ["id"], ["onDelete" => "SET NULL"], "FK_BC91F4161A36181E");
        $resourceTable->addForeignKeyConstraint("user_accounts", ["owner_id"], ["id"], ["onDelete" => "SET NULL"], "FK_BC91F4167E3C61F9");
        $resourceTable->addForeignKeyConstraint("user_group", ["group_id"], ["id"], ["onDelete" => "SET NULL"], "FK_BC91F416FE54D947");

        $resourceFileTable = $schema->createTable("resource_file");
        $resourceFileTable->addColumn("resource_id", Types::GUID)
            ->setNotnull(false)
            ->setComment("(DC2Type:ulid)");
        $resourceFileTable->addColumn("file_id", Types::GUID)
            ->setNotnull(false)
            ->setComment("(DC2Type:ulid)");
        $resourceFileTable->setPrimaryKey(["resource_id", "file_id"]);
        $resourceFileTable->addIndex(["resource_id"], "IDX_83BF96AA89329D25");
        $resourceFileTable->addUniqueIndex(["file_id"], "UNIQ_83BF96AA93CB796C");

        $resourceFileTable->addForeignKeyConstraint("resource", ["resource_id"], ["id"], ["onDelete" => "CASCADE"], "FK_83BF96AA89329D25");
        $resourceFileTable->addForeignKeyConstraint("file", ["file_id"], ["id"], ["onDelete" => "CASCADE"], "FK_83BF96AA93CB796C");
    }

    public function down(Schema $schema): void
    {
        $resourceTable = $schema->getTable("resource");
        $resourceTable->removeForeignKey("FK_BC91F4161A36181E");
        $resourceTable->removeForeignKey("FK_BC91F4167E3C61F9");
        $resourceTable->removeForeignKey("FK_BC91F416FE54D947");

        $resourceFileTable = $schema->getTable("resource_file");
        $resourceFileTable->removeForeignKey("FK_83BF96AA89329D25");
        $resourceFileTable->removeForeignKey("FK_83BF96AA93CB796C");

        $schema->dropTable("resource");
        $schema->dropTable("resource_file");
    }
}
