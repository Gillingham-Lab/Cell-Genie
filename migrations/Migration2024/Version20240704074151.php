<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use App\Genie\Enums\PrivacyLevel;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20240704074151 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds experimental design to run relationship, and makes experimental runs privacy aware.';
    }

    public function up(Schema $schema): void
    {
        $runTable = $schema->getTable('new_experimental_run');
        $runTable->addColumn("owner_id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");
        $runTable->addColumn("group_id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");
        $runTable->addColumn("privacy_level", Types::SMALLINT)->setNotnull(true)->setDefault(PrivacyLevel::Group->value);
        $runTable->addColumn("design_id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");

        $runTable->addForeignKeyConstraint("new_experimental_design", ["design_id"], ["id"], ["onDelete" => "CASCADE"], "FK_14F2646AE41DC9B2");
        $runTable->addForeignKeyConstraint("user_accounts", ["owner_id"], ["id"], ["onDelete" => "CASCADE"], "FK_14F2646A7E3C61F9");
        $runTable->addForeignKeyConstraint("user_group", ["group_id"], ["id"], ["onDelete" => "CASCADE"], "FK_14F2646AFE54D947");

        $runTable->addIndex(["design_id"], "IDX_14F2646AE41DC9B2");
        $runTable->addIndex(["owner_id"], "IDX_14F2646A7E3C61F9");
        $runTable->addIndex(["group_id"], "IDX_14F2646AFE54D947");
    }

    public function down(Schema $schema): void
    {
        $runTable = $schema->getTable('new_experimental_run');
        $runTable->removeForeignKey("FK_14F2646AE41DC9B2");
        $runTable->removeForeignKey("FK_14F2646A7E3C61F9");
        $runTable->removeForeignKey("FK_14F2646AFE54D947");
        $runTable->dropIndex("IDX_14F2646AE41DC9B2");
        $runTable->dropIndex("IDX_14F2646A7E3C61F9");
        $runTable->dropIndex("IDX_14F2646AFE54D947");
        $runTable->dropColumn("design_id");
        $runTable->dropColumn("owner_id");
        $runTable->dropColumn("group_id");
        $runTable->dropColumn("privacy_level");
    }
}
