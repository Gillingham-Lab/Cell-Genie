<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220926062133 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Removes field epitope type from epitope table.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("epitope");
        $table->dropColumn("epitope_type");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
