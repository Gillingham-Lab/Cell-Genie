<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use App\Doctrine\Migration\AddIdColumnTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20240418124344 extends AbstractMigration
{
    use AddIdColumnTrait;

    public function getDescription(): string
    {
        return 'Creates tables for the new experiment module';
    }

    public function up(Schema $schema): void
    {
        $datumTable = $schema->createTable("new_experimental_datum");
        $this->addIdColumn($datumTable);
        $datumTable->addColumn("name", Types::STRING)
            ->setNotnull(true)
            ->setLength(255);
        $datumTable->addColumn("type", Types::STRING)
            ->setNotnull(true)
            ->setLength(255);
        $datumTable->addColumn("value", Types::BINARY)
            ->setLength(256)
            ->setNotnull(true);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable("new_experimental_datum");
    }
}
