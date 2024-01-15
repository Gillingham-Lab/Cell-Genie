<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20240115142434 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds attachment functionality to consumables.';
    }

    public function up(Schema $schema): void
    {
        $fileTable = $schema->createTable("consumable_file");
        $fileTable->addColumn("consumable_id", Types::GUID)
            ->setNotnull(true)
            ->setComment("(DC2Type:ulid)");
        $fileTable->addColumn("file_id", Types::GUID)
            ->setNotnull(true)
            ->setComment("(DC2Type:ulid)");
        $fileTable->setPrimaryKey(["consumable_id", "file_id"]);
        $fileTable->addIndex(["consumable_id"], "IDX_8815D876A94ADB61");
        $fileTable->addUniqueIndex(["file_id"], "UNIQ_8815D87693CB796C");

        $fileTable->addForeignKeyConstraint("consumable", ["consumable_id"], ["id"], ["onDelete" => "CASCADE"], "FK_8815D876A94ADB61");
        $fileTable->addForeignKeyConstraint("file", ["file_id"], ["id"], ["onDelete" => "CASCADE"], "FK_8815D87693CB796C");
    }

    public function down(Schema $schema): void
    {
        $fileTable = $schema->getTable("consumable_file");
        $fileTable->removeForeignKey("FK_8815D876A94ADB61");
        $fileTable->removeForeignKey("FK_8815D87693CB796C");
        $schema->dropTable("consumable_file");
    }
}
