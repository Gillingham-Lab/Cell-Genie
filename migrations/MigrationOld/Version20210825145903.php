<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210825145903 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable("cell_file");
        $table->addColumn("cell_id", "integer")
            ->setNotnull(true)
        ;
        $table->addColumn("file_id", "guid")
            ->setNotnull(true)
            ->setComment("(DC2Type:ulid)")
        ;
        $table->setPrimaryKey(["cell_id", "file_id"]);
        $table->addIndex(["cell_id"], indexName: "IDX_3F545FB6CB39D93A");
        $table->addUniqueIndex(["file_id"], indexName: "UNIQ_3F545FB693CB796C");

        $table->addForeignKeyConstraint("cell", ["cell_id"], ["id"], options: ["onDelete" => "cascade"], name: "FK_3F545FB6CB39D93A");
        $table->addForeignKeyConstraint("file", ["file_id"], ["id"], name: "FK_3F545FB693CB796C");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable("cell_file");
    }
}
