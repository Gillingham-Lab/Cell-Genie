<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221014072358 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds an order_value to cell_protein table.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("cell_protein");
        $table->addColumn("order_value", "integer")
            ->setDefault(0)
            ->setNotnull(true);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("cell_protein");
        $table->dropColumn("order_value");
    }
}
