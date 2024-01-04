<?php
declare(strict_types=1);

namespace DoctrineMigrations2023;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20230816063320 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates tables for stock keeping.';
    }

    public function up(Schema $schema): void
    {
        $consumableTable = $schema->createTable("consumable");
        $consumableTable->addColumn("id", Types::GUID)->setNotnull(true)->setComment("(DC2Type:ulid)");
        $consumableTable->addColumn("category_id", Types::GUID)->setNotnull(true)->setComment("(DC2Type:ulid)");
        $consumableTable->addColumn("long_name", Types::STRING)->setLength(255)->setNotnull(true);
        $consumableTable->addColumn("product_number", Types::STRING)->setLength(255)->setNotnull(true);

        $consumableTable->addColumn("owner_id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");
        $consumableTable->addColumn("group_id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");
        $consumableTable->addColumn("privacy_level", Types::SMALLINT)->setNotnull(true)->setDefault(2);

        $consumableTable->addColumn("vendor_id", Types::INTEGER)->setNotnull(false);
        $consumableTable->addColumn("vendor_pn", Types::STRING)->setLength(255)->setNotnull(false);

        $consumableTable->addColumn("consume_package", Types::BOOLEAN)->setNotnull(true);
        $consumableTable->addColumn("ideal_stock", Types::INTEGER)->setNotnull(true);
        $consumableTable->addColumn("order_limit", Types::INTEGER)->setNotnull(true);
        $consumableTable->addColumn("critical_limit", Types::INTEGER)->setNotnull(true);
        $consumableTable->addColumn("expected_delivery_time", Types::STRING)->setLength(255)->setNotnull(true);
        $consumableTable->addColumn("unit_size", Types::INTEGER)->setNotnull(true);
        $consumableTable->addColumn("number_of_units", Types::INTEGER)->setNotnull(true);
        $consumableTable->addColumn("price_per_package", Types::FLOAT)->setNotnull(true);
        $consumableTable->addColumn("location_ulid", "guid")->setNotnull(false)->setComment("(DC2Type:ulid)");

        $consumableTable->addColumn("comment", Types::TEXT)->setNotnull(false);

        $consumableTable->setPrimaryKey(["id"]);
        $consumableTable->addIndex(["category_id"], "IDX_4475F09512469DE2");
        $consumableTable->addIndex(["owner_id"], "IDX_4475F0957E3C61F9");
        $consumableTable->addIndex(["group_id"], "IDX_4475F095FE54D947");
        $consumableTable->addIndex(["vendor_id"], "IDX_4475F095F603EE73");
        $consumableTable->addIndex(["location_ulid"], "IDX_4475F095428C7D19");

        $consumableCategoryTable = $schema->createTable("consumable_category");
        $consumableCategoryTable->addColumn("id", Types::GUID)->setNotnull(true)->setComment("(DC2Type:ulid)");
        $consumableCategoryTable->addColumn("parent_id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");
        $consumableCategoryTable->addColumn("show_units", Types::BOOLEAN)->setNotnull(true);
        $consumableCategoryTable->addColumn("ideal_stock", Types::INTEGER)->setNotnull(true);
        $consumableCategoryTable->addColumn("order_limit", Types::INTEGER)->setNotnull(true);
        $consumableCategoryTable->addColumn("critical_limit", Types::INTEGER)->setNotnull(true);
        $consumableCategoryTable->addColumn("long_name", Types::STRING)->setNotnull(true);
        $consumableCategoryTable->addColumn("comment", Types::TEXT)->setNotnull(false);

        $consumableCategoryTable->addColumn("owner_id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");
        $consumableCategoryTable->addColumn("group_id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");
        $consumableCategoryTable->addColumn("privacy_level", Types::SMALLINT)->setNotnull(true)->setDefault(2);

        $consumableCategoryTable->setPrimaryKey(["id"]);
        $consumableCategoryTable->addIndex(["parent_id"], "IDX_7D7CF86C727ACA70");
        $consumableCategoryTable->addIndex(["owner_id"], "IDX_7D7CF86C7E3C61F9");
        $consumableCategoryTable->addIndex(["group_id"], "IDX_7D7CF86CFE54D947");

        $consumableLotTable = $schema->createTable("consumable_lot");
        $consumableLotTable->addColumn("id", Types::GUID)->setNotnull(true)->setComment("(DC2Type:ulid)");
        $consumableLotTable->addColumn("consumable_id", Types::GUID)->setNotnull(true)->setComment("(DC2Type:ulid)");
        $consumableLotTable->addColumn("bought_by_id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");
        $consumableLotTable->addColumn("availability", Types::STRING)->setLength(255)->setNotnull(true)->setDefault("available");
        $consumableLotTable->addColumn("bought_on", Types::DATE_MUTABLE)->setNotnull(true);
        $consumableLotTable->addColumn("arrived_on", Types::DATE_MUTABLE)->setNotnull(false);
        $consumableLotTable->addColumn("opened_on", Types::DATE_MUTABLE)->setNotnull(false);
        $consumableLotTable->addColumn("unit_size", Types::INTEGER)->setNotnull(true);
        $consumableLotTable->addColumn("number_of_units", Types::INTEGER)->setNotnull(true);
        $consumableLotTable->addColumn("units_consumed", Types::INTEGER)->setNotnull(true);
        $consumableLotTable->addColumn("pieces_consumed", Types::INTEGER)->setNotnull(true);
        $consumableLotTable->addColumn("price_per_package", Types::FLOAT)->setNotnull(true);
        $consumableLotTable->addColumn("location_ulid", "guid")->setNotnull(false)->setComment("(DC2Type:ulid)");

        $consumableLotTable->setPrimaryKey(["id"]);
        $consumableLotTable->addIndex(["consumable_id"], "IDX_5A495A9DA94ADB61");
        $consumableLotTable->addIndex(["bought_by_id"], "IDX_5A495A9DDEC6D6BA");
        $consumableLotTable->addIndex(["location_ulid"], "IDX_5A495A9D428C7D19");

        $consumableTable->addForeignKeyConstraint("consumable_category", ["category_id"], ["id"], [], "FK_4475F09512469DE2");
        $consumableTable->addForeignKeyConstraint("user_accounts", ["owner_id"], ["id"], ["onDelete" => "SET NULL"], "FK_4475F0957E3C61F9");
        $consumableTable->addForeignKeyConstraint("user_group", ["group_id"], ["id"], ["onDelete" => "SET NULL"], "FK_4475F095FE54D947");
        $consumableTable->addForeignKeyConstraint("vendor", ["vendor_id"], ["id"], ["onDelete" => "SET NULL"], "FK_4475F095F603EE73");
        $consumableTable->addForeignKeyConstraint("rack", ["location_ulid"], ["ulid"], ["onDelete" => "SET NULL"], "FK_4475F095428C7D19");

        $consumableCategoryTable->addForeignKeyConstraint("consumable_category", ["parent_id"], ["id"], ["onDelete" => "SET NULL"], "FK_7D7CF86C727ACA70");
        $consumableCategoryTable->addForeignKeyConstraint("user_accounts", ["owner_id"], ["id"], ["onDelete" => "SET NULL"], "FK_7D7CF86C7E3C61F9");
        $consumableCategoryTable->addForeignKeyConstraint("user_group", ["group_id"], ["id"], ["onDelete" => "SET NULL"], "FK_7D7CF86CFE54D947");

        $consumableLotTable->addForeignKeyConstraint("consumable", ["consumable_id"], ["id"], ["onDelete" => "CASCADE"], "FK_5A495A9DA94ADB61");
        $consumableLotTable->addForeignKeyConstraint("user_accounts", ["bought_by_id"], ["id"], ["onDelete" => "SET NULL"], "FK_5A495A9DDEC6D6BA");
        $consumableLotTable->addForeignKeyConstraint("rack", ["location_ulid"], ["ulid"], ["onDelete" => "SET NULL"], "FK_5A495A9D428C7D19");
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("consumable");
        $table->removeForeignKey("FK_4475F09512469DE2");
        $table->removeForeignKey("FK_4475F0957E3C61F9");
        $table->removeForeignKey("FK_4475F0957E3C61F9");
        $table->removeForeignKey("FK_4475F0957E3C61F9");

        $table = $schema->getTable("consumable_category");
        $table->removeForeignKey("FK_7D7CF86C727ACA70");
        $table->removeForeignKey("FK_7D7CF86C7E3C61F9");
        $table->removeForeignKey("FK_7D7CF86CFE54D947");

        $table = $schema->getTable("consumable_lot");
        $table->removeForeignKey("FK_5A495A9DA94ADB61");
        $table->removeForeignKey("FK_5A495A9DDEC6D6BA");

        $schema
            ->dropTable("consumable")
            ->dropTable("consumable_category")
            ->dropTable("consumable_lot")
        ;
    }
}
