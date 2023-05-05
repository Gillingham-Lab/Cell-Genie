<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221101075017 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds box coordinate and performs some missing migrations.';
    }


    public function up(Schema $schema): void
    {
        $table = $schema->getTable("lot");
        $table->addColumn("box_coordinate", "string")->setNotnull(false)->setLength(10);

        $table = $schema->getTable("oligo");
        $table->getColumn("sequence")->setNotnull(false);
        $table->getColumn("sequence_length")->setNotnull(false);

        $table = $schema->getTable("sequence_annotation");
        $table->getColumn("annotation_start")->setNotnull(true);
        $table->getColumn("annotation_end")->setNotnull(true);
        $table->getColumn("annotations")->setNotnull(false);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("sequence_annotation");
        $table->getColumn("annotation_start")->setNotnull(false);
        $table->getColumn("annotation_end")->setNotnull(false);
        $table->getColumn("annotations")->setNotnull(true);

        $table = $schema->getTable("oligo");
        $table->getColumn("sequence")->setNotnull(true);
        $table->getColumn("sequence_length")->setNotnull(true);

        $table = $schema->getTable("lot");
        $table->dropColumn("box_coordinate");
    }
}
