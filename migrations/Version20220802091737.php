<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220802091737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("antibody");
        $table->removeForeignKey("FK_5C97C6B11D6C21C8");
        $table->addForeignKeyConstraint("epitope_host", ["host_organism_id"], ["id"], [], "FK_5C97C6B11D6C21C8");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
