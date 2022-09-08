<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220907122100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a new substance<->lot association table to have aliquots for all substances.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable("substance_lots");
        $table->addColumn("substance_ulid", "guid")->setNotnull(true)->setComment("(DC2Type:ulid)");
        $table->addColumn("lot_id", "guid")->setNotnull(true)->setComment("(DC2Type:ulid)");

        $table->addForeignKeyConstraint("substance", ["substance_ulid"], ["ulid"], [], "FK_DA1C2C7E89F463E9");
        $table->addForeignKeyConstraint("lot", ["lot_id"], ["id"], [], "FK_DA1C2C7EA8CBA5F7");

        $table->setPrimaryKey(["substance_ulid", "lot_id"]);
        $table->addIndex(["substance_ulid"], "IDX_DA1C2C7E89F463E9");
        $table->addUniqueIndex(["lot_id"], "UNIQ_DA1C2C7EA8CBA5F7");
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("substance_lots");
        $table->removeForeignKey("FK_DA1C2C7E89F463E9");
        $table->removeForeignKey("FK_DA1C2C7EA8CBA5F7");
        $table->dropIndex("IDX_DA1C2C7E89F463E9");
        $table->dropIndex("UNIQ_DA1C2C7EA8CBA5F7");
        $schema->dropTable("substance_lots");
    }
}
