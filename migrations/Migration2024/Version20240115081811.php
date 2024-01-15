<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20240115081811 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a lot identifier field as well as a vendor lot number to consumable lots';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("consumable_lot");
        $table->addColumn("lot_identifier", Types::STRING)
            ->setLength(255)
            ->setNotnull(false);
        $table->addColumn("lot_number", Types::STRING)
            ->setLength(255)
            ->setNotnull(false);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("consumable_lot");
        $table->dropColumn("lot_identifier");
        $table->dropColumn("lot_number");
    }
}
