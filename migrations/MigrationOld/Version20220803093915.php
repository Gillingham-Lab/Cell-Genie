<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220803093915 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds chemical.iupacName and epitope.Description';
    }

    public function up(Schema $schema): void
    {
        $schema->getTable("chemical")
            ->addColumn("iupac_name", "text")->setNotnull(false);

        $schema->getTable("epitope")
            ->addColumn("description", "text")->setNotnull(false);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
