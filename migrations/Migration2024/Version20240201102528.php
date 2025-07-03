<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20240201102528 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Removes the old price.';
    }

    private function getTables(): array
    {
        return [
            ["cell", "price", "price"],
            ["consumable", "price_per_package", "price_per_package"],
            ["consumable_lot", "price_per_package", "price_per_package"],
        ];
    }

    private function addOldPriceColumn(Table $table, string $oldColumnName): void
    {
        $table->addColumn($oldColumnName, Types::DECIMAL)
            ->setPrecision(7)
            ->setScale(2)
            ->setNotnull(false)
        ;
    }

    private function updateToOldPrice(string $tableName, string $columnPrefix, string $oldColumnName): void
    {
        $this->connection->createQueryBuilder()
            ->update($tableName)
            ->set($oldColumnName, "CASE WHEN {$columnPrefix}_price_value IS NULL THEN NULL ELSE {$columnPrefix}_price_value / 1000 END")
            ->executeQuery();
    }

    private function removeOldPriceColumn(Table $table, string $oldColumnName): void
    {
        $table->dropColumn($oldColumnName);
    }

    public function up(Schema $schema): void
    {
        foreach ($this->getTables() as [$tableName, $columnPrefix, $oldColumnName]) {
            $this->removeOldPriceColumn($schema->getTable($tableName), $oldColumnName);
        }
    }

    public function down(Schema $schema): void
    {
        foreach ($this->getTables() as [$tableName, $columnPrefix, $oldColumnName]) {
            $table = $schema->getTable($tableName);
            $this->addOldPriceColumn($table, $columnPrefix);
        }
    }

    public function postDown(Schema $schema): void
    {
        foreach ($this->getTables() as [$tableName, $columnPrefix, $oldColumnName]) {
            $this->updateToOldPrice($tableName, $columnPrefix, $oldColumnName);
        }
    }
}
