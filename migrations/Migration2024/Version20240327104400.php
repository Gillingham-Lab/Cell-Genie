<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240327104400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Changes the pin_code of rack to not null.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("rack");
        $table->getColumn("pin_code")->setNotnull(false);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("rack");
        $table->getColumn("pin_code")->setNotnull(true);
    }
}
