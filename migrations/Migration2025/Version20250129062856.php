<?php
declare(strict_types=1);

namespace DoctrineMigrations2025;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250129062856 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates tables for project and project users.';
    }

    public function up(Schema $schema): void
    {
        $projectTable = $schema->createTable("project");
        $projectTable->addColumn("id", Types::GUID, ["notnull" => true, "comment" => "(DC2Type:ulid)"]);
        $projectTable->addColumn("owner_id", Types::GUID, ["notnull" => false, "comment" => "(DC2Type:ulid)"]);
        $projectTable->addColumn("group_id", Types::GUID, ["notnull" => false, "comment" => "(DC2Type:ulid)"]);
        $projectTable->addColumn("short_name", Types::STRING, ["length" => 20, "notnull" => true]);
        $projectTable->addColumn("name", Types::STRING, ["length" => 255, "notnull" => true]);
        $projectTable->addColumn("settings", Types::JSON, ["notnull" => false, "comment" => "(DC2Type:json_document)"]);
        $projectTable->addColumn("comment", Types::TEXT, ["notnull" => false]);
        $projectTable->addColumn("privacy_level", Types::SMALLINT, ["notnull" => true, "default" => 2]);
        $projectTable->setPrimaryKey(["id"]);
        $projectTable->addIndex(["owner_id"], "IDX_2FB3D0EE7E3C61F9");
        $projectTable->addIndex(["group_id"], "IDX_2FB3D0EEFE54D947");

        $projectUserTable = $schema->createTable("project_user");
        $projectUserTable->addColumn("id", Types::GUID, ["notnull" => true, "comment" => "(DC2Type:ulid)"]);
        $projectUserTable->addColumn("project_id", Types::GUID, ["notnull" => false, "comment" => "(DC2Type:ulid)"]);
        $projectUserTable->addColumn("user_id", Types::GUID, ["notnull" => false, "comment" => "(DC2Type:ulid)"]);
        $projectUserTable->setPrimaryKey(["id"]);
        $projectUserTable->addIndex(["project_id"], "IDX_B4021E51166D1F9C");
        $projectUserTable->addIndex(["user_id"], "IDX_B4021E51A76ED395");

        $projectTable->addForeignKeyConstraint("user_accounts", ["owner_id"], ["id"], ["onDelete" => "SET NULL"], "FK_2FB3D0EE7E3C61F9");
        $projectTable->addForeignKeyConstraint("user_group", ["group_id"], ["id"], ["onDelete" => "SET NULL"], "FK_2FB3D0EEFE54D947");

        $projectUserTable->addForeignKeyConstraint("project", ["project_id"], ["id"], ["onDelete" => "CASCADE"], "FK_B4021E51166D1F9C");
        $projectUserTable->addForeignKeyConstraint("user_accounts", ["user_id"], ["id"], ["onDelete" => "CASCADE"], "FK_B4021E51A76ED395");
    }

    public function down(Schema $schema): void
    {
        $projectTable = $schema->getTable("project");
        $projectTable->removeForeignKey("FK_2FB3D0EE7E3C61F9");
        $projectTable->removeForeignKey("FK_2FB3D0EEFE54D947");

        $projectUserTable = $schema->getTable("project_user");
        $projectUserTable->removeForeignKey("FK_B4021E51166D1F9C");
        $projectUserTable->removeForeignKey("FK_B4021E51A76ED395");

        $schema->dropTable("project");
        $schema->dropTable("project_user");
    }
}
