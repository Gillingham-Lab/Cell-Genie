<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220907103140 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates a new table for oligos';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable("oligo");
        $table->addColumn("ulid", "guid")->setNotnull(true)->setComment("(DC2Type:ulid)");
        $table->addColumn("sequence", "text")->setNotnull(true);
        $table->addColumn("sequence_length", "integer")->setNotnull(true);
        $table->addColumn("concentration", "float")->setNotnull(false);
        $table->addColumn("amount_ordered", "float")->setNotnull(false);
        $table->addColumn("amount_left", "float")->setNotnull(false);
        $table->addColumn("extinction_coefficient", "float")->setNotnull(false);
        $table->addColumn("purification", "string")->setNotnull(false);
        $table->addColumn("comment", "text")->setNotnull(false);
        $table->addColumn("molecular_mass", "float")->setNotnull(true)->setDefault(0.0);
        $table->addColumn("labjournal", "text")->setNotnull(false);

        $table->setPrimaryKey(["ulid"]);

        $table->addForeignKeyConstraint("substance", ["ulid"], ["ulid"], ["onDelete" => "CASCADE"], "FK_1C072E3CC288C859");
    }

    public function down(Schema $schema): void
    {
        $schema->getTable("oligo")->removeForeignKey("FK_1C072E3CC288C859");
        $schema->dropTable("oligo");
    }
}
