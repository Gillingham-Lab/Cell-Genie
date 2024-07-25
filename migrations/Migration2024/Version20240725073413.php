<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20240725073413 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migration clean-up';
    }

    public function up(Schema $schema): void
    {
        $schema->getTable("cell")
            ->dropColumn("parent_ulid");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
