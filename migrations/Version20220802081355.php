<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220802081355 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $schema->dropTable("epitope_host_antibody_host");

        $schema->getTable("antibody")
            ->addColumn("ulid", "guid")->setNotnull(false)->setComment("(DC2Type:ulid)");

        $schema->dropTable("antibody_dilution");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
