<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220915054927 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates a table for barcodes';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable("barcode");
        $table->addColumn("id", "guid")
            ->setNotnull(true)->setComment("(DC2Type:ulid)");
        $table->addColumn("barcode", "string")
            ->setNotnull(true)->setLength(255);
        $table->addColumn("referenced_table", "string")
            ->setNotnull(true)->setLength(255);
        $table->addColumn("referenced_id", "string")
            ->setNotnull(true)->setLength(255);
        $table->setPrimaryKey(["id"]);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable("barcode");
    }
}
