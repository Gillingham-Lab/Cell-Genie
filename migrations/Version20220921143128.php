<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220921143128 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create substance epitope table.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable("substance_epitopes");
        $table->addColumn("substance_ulid", "guid")
            ->setNotnull(true)->setComment("(DC2Type:ulid)");;
        $table->addColumn("epitope_id", "guid")
            ->setNotnull(true)->setComment("(DC2Type:ulid)");;

        $table->setPrimaryKey(["substance_ulid", "epitope_id"]);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable("substance_epitopes");
    }
}
