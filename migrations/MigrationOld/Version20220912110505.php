<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220912110505 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Cleans up left-over database fields';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("antibody_lots");
        $table->removeForeignKey("fk_5c96db86a8cba5f7");
        $table->removeForeignKey("fk_5c96db86f7e9411");

        $schema->dropTable("antibody_lots");

        $table = $schema->getTable("antibody");
        $table->dropColumn("short_name");
        $table->dropColumn("long_name");
        $table->dropColumn("host_target_id");

        $table = $schema->getTable("cell_culture");
        $table->getColumn("number")->setNotnull(false);

        $table = $schema->getTable("chemical");
        $table->dropColumn("id");
        $table->dropColumn("long_name");
        $table->dropColumn("short_name");

        $table = $schema->getTable("protein");
        $table->dropColumn("id");
        $table->dropColumn("long_name");
        $table->dropColumn("short_name");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
