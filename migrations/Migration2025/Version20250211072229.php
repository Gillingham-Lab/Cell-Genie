<?php
declare(strict_types=1);

namespace DoctrineMigrations2025;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250211072229 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a "referenced" field to the new_experimental_design_field table.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("new_experimental_design_field");
        $table->addColumn("referenced", Types::BOOLEAN, [
            "notNull" => true,
            "default" => false,
        ]);

        $table->addColumn("reference_value", Types::STRING, [
            "notNull" => false,
            "length" => 255,
            "default" => null,
        ]);

        $table = $schema->getTable("experimental_model");
        $table->addColumn("reference_model", Types::STRING, ["notnull" => false, "length" => 255, "default" => null]);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("new_experimental_design_field");
        $table->dropColumn("referenced");
        $table->dropColumn("reference_value");

        $table = $schema->getTable("experimental_model");
        $table->dropColumn("reference_model");
    }
}
