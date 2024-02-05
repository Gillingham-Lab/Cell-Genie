<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20240202112650 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds privacy levels to vendors';
    }

    public function up(Schema $schema): void
    {
        $vendor = $schema->getTable("vendor");
        $vendor->addColumn("owner_id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");
        $vendor->addColumn("group_id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");
        $vendor->addColumn("privacy_level", Types::SMALLINT)->setNotnull(true)->setDefault(2);
        $vendor->addIndex(["owner_id"], "IDX_F52233F67E3C61F9");
        $vendor->addIndex(["group_id"], "IDX_F52233F6FE54D947");
        $vendor->addForeignKeyConstraint("user_accounts", ["owner_id"], ["id"], ["onDelete" => "SET NULL"], "FK_F52233F67E3C61F9");
        $vendor->addForeignKeyConstraint("user_group", ["group_id"], ["id"], ["onDelete" => "SET NULL"], "FK_F52233F6FE54D947");
    }

    public function down(Schema $schema): void
    {
        $vendor = $schema->getTable("vendor");
        $vendor->dropIndex("IDX_F52233F67E3C61F9");
        $vendor->dropIndex("IDX_F52233F6FE54D947");
        $vendor->removeForeignKey("FK_F52233F67E3C61F9");
        $vendor->removeForeignKey("FK_F52233F6FE54D947");
        $vendor->dropColumn("owner_id");
        $vendor->dropColumn("group_id");
        $vendor->dropColumn("privacy_level");
    }
}
