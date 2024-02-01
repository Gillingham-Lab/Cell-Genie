<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20240201102527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a priceValue and priceCurrency field to entries with a price and updates the priceValue.';
    }

    private function getTables(): array
    {
        return [
            ["cell", "price", "price"],
            ["consumable", "price_per_package", "price_per_package"],
            ["consumable_lot", "price_per_package", "price_per_package"],
        ];
    }

    private function addNewPriceColumns(Table $table, string $columnPrefix): void
    {
        $table->addColumn("{$columnPrefix}_price_value", Types::INTEGER)
            ->setNotnull(false);
        $table->addColumn("{$columnPrefix}_price_currency", Types::STRING)
            ->setNotnull(false)
            ->setLength(3)
        ;
    }

    private function updateToNewPrice(string $tableName, string $columnPrefix, string $oldColumnName): void
    {
        $this->connection->createQueryBuilder()
            ->update($tableName)
            ->set("{$columnPrefix}_price_value", "CASE WHEN {$oldColumnName} IS NULL THEN NULL ELSE {$oldColumnName} * 1000 END")
            ->executeQuery();
        ;
    }

    private function removeNewPriceColumns(Table $table, string $columnPrefix): void
    {
        $table->dropColumn("{$columnPrefix}_price_value");
        $table->dropColumn("{$columnPrefix}_price_currency");
    }


    public function up(Schema $schema): void
    {
        foreach ($this->getTables() as [$tableName, $columnPrefix, $oldColumnName]) {
            $table = $schema->getTable($tableName);
            $this->addNewPriceColumns($table, $columnPrefix);
        }
    }

    public function postUp(Schema $schema): void
    {
        foreach ($this->getTables() as [$tableName, $columnPrefix, $oldColumnName]) {
            $this->updateToNewPrice($tableName, $columnPrefix, $oldColumnName);
        }
    }

    public function down(Schema $schema): void
    {
        foreach ($this->getTables() as [$tableName, $columnPrefix, $oldColumnName]) {
            $this->removeNewPriceColumns($schema->getTable($tableName), $columnPrefix);
        }
    }
}
