<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use App\Doctrine\Migration\AddIdColumnTrait;
use App\Genie\Enums\PrivacyLevel;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20240703094829 extends AbstractMigration
{
    use AddIdColumnTrait;

    public function getDescription(): string
    {
        return 'Creates tables for experimental designs';
    }

    public function up(Schema $schema): void
    {
        $formRowTable = $schema->createTable("form_row");
        $this->addIdColumn($formRowTable);
        $formRowTable->addColumn("label", Types::STRING)->setLength(255)->setNotnull(true);
        $formRowTable->addColumn("help", Types::TEXT)->setNotnull(false);
        $formRowTable->addColumn("type", Types::STRING)->setLength(255)->setNotnull(true);
        $formRowTable->addColumn("configuration", Types::JSON)->setNotnull(true);

        // Experimental Design
        $experimentalDesignTable = $schema->createTable("new_experimental_design");
        $this->addIdColumn($experimentalDesignTable);
        $experimentalDesignTable->addColumn("owner_id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");
        $experimentalDesignTable->addColumn("group_id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");
        $experimentalDesignTable->addColumn("short_name", Types::STRING)->setLength(50)->setNotnull(true);
        $experimentalDesignTable->addColumn("long_name", Types::STRING)->setLength(255)->setNotnull(true);
        $experimentalDesignTable->addColumn("number", Types::STRING)->setLength(10)->setNotnull(true)->setDefault("???");
        $experimentalDesignTable->addColumn("privacy_level", Types::SMALLINT)->setNotnull(true)->setDefault(PrivacyLevel::Group);

        $experimentalDesignTable->addUniqueIndex(["short_name"], "UNIQ_388F12B53EE4B093");
        $experimentalDesignTable->addIndex(["owner_id"], "IDX_388F12B57E3C61F9");
        $experimentalDesignTable->addIndex(["group_id"], "IDX_388F12B5FE54D947");

        // Experimental Design Fields
        $experimentalDesignFieldTable = $schema->createTable("new_experimental_design_field");
        $this->addIdColumn($experimentalDesignFieldTable);
        $experimentalDesignFieldTable->addColumn("design_id", Types::GUID)->setNotnull(true)->setComment("(DC2Type:ulid)");
        $experimentalDesignFieldTable->addColumn("form_row_id", Types::GUID)->setNotnull(true)->setComment("(DC2Type:ulid)");
        $experimentalDesignFieldTable->addColumn("role", Types::STRING)->setLength(255)->setNotnull(true);
        $experimentalDesignFieldTable->addColumn("weight", Types::SMALLINT)->setNotnull(false)->setDefault(0);

        $experimentalDesignFieldTable->addIndex(["design_id"], "IDX_1EA8FDA5E41DC9B2");
        $experimentalDesignFieldTable->addUniqueIndex(["form_row_id"], "UNIQ_1EA8FDA5814CBD5C");

        // Foreign keys
        $experimentalDesignTable->addForeignKeyConstraint("user_accounts", ["owner_id"], ["id"], ["onDelete" => "SET NULL"], "FK_388F12B57E3C61F9");
        $experimentalDesignTable->addForeignKeyConstraint("user_group", ["group_id"], ["id"], ["onDelete" => "SET NULL"], "FK_388F12B5FE54D947");

        $experimentalDesignFieldTable->addForeignKeyConstraint("new_experimental_design", ["design_id"], ["id"], ["onDelete" => "CASCADE"], "FK_1EA8FDA5E41DC9B2");
        $experimentalDesignFieldTable->addForeignKeyConstraint("form_row", ["form_row_id"], ["id"], ["onDelete" => "CASCADE"], "FK_1EA8FDA5814CBD5C");

        $experimentalRunTable = $schema->getTable("new_experimental_run");
        $experimentalRunTable->getColumn("created_at")->setType(Type::getType(Types::DATETIME_MUTABLE))->setComment(null);
    }

    public function down(Schema $schema): void
    {
        $experimentalDesignTable = $schema->getTable("new_experimental_design");
        $experimentalDesignTable->removeForeignKey("FK_388F12B57E3C61F9");
        $experimentalDesignTable->removeForeignKey("FK_388F12B5FE54D947");

        $experimentalDesignTable = $schema->getTable("new_experimental_design_field");
        $experimentalDesignTable->removeForeignKey("FK_1EA8FDA5E41DC9B2");
        $experimentalDesignTable->removeForeignKey("FK_1EA8FDA5814CBD5C");

        $schema->dropTable("form_row");
        $schema->dropTable("new_experimental_design");
        $schema->dropTable("new_experimental_design_field");

        $experimentalRunTable = $schema->getTable("new_experimental_run");
        $experimentalRunTable->getColumn("created_at")->setType(Type::getType(Types::DATETIME_IMMUTABLE))->setComment("(DC2Type:datetime_immutable)");
    }
}
