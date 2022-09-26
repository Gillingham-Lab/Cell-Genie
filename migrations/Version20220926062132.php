<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220926062132 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds FK constraints for substance_epitopes and a new antibody type field. Adds unique constraint on barcode field.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("antibody");
        $table->addColumn("type", "string")->setLength(255)->setDefault("primary")->setNotnull(true);

        $table = $schema->getTable("substance_epitopes");
        $table->addForeignKeyConstraint("epitope", ["epitope_id"], ["id"], options: ["onDelete" => "CASCADE"], name: "FK_7E01DB9866EC6D6C");
        $table->addForeignKeyConstraint("substance", ["substance_ulid"], ["ulid"], options: ["onDelete" => "CASCADE"], name: "FK_7E01DB9889F463E9");

        $table = $schema->getTable("barcode");
        $table->addUniqueIndex(["barcode"], "UNIQ_97AE026697AE0266");
    }

    public function down(Schema $schema): void
    {
        $schema->getTable("antibody")->dropColumn("type");
        $schema->getTable("barcode")->dropIndex("UNIQ_97AE026697AE0266");
        $table = $schema->getTable("substance_epitopes");
        $table->removeForeignKey("FK_7E01DB9866EC6D6C");
        $table->removeForeignKey("FK_7E01DB9889F463E9");
    }
}
