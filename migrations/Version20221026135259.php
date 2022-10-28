<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;

final class Version20221026135259 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a sequence annotation table.';
    }

    public function up(Schema $schema): void
    {
        $sequenceAnnotationTable = $schema->createTable("sequence_annotation");
        $sequenceAnnotationTable->addColumn("id", "guid")->setNotnull(true)->setComment("(DC2Type:ulid)");
        $sequenceAnnotationTable->addColumn("annotation_label", "string")->setLength(50)->setNotnull(true);
        $sequenceAnnotationTable->addColumn("annotation_type", "string")->setLength(50)->setNotnull(true);
        $sequenceAnnotationTable->addColumn("color", "string")->setLength(50)->setNotnull(false);
        $sequenceAnnotationTable->addColumn("is_complement", "boolean")->setNotnull(true)->setDefault(false);
        $sequenceAnnotationTable->addColumn("annotation_start", "integer")->setNotnull(false);
        $sequenceAnnotationTable->addColumn("annotation_end", "integer")->setNotnull(false);
        $sequenceAnnotationTable->addColumn("annotations", "text")->setNotnull(true)->setComment("(DC2Type:array)");
        $sequenceAnnotationTable->addColumn("comment", "text")->setNotnull(false);
        $sequenceAnnotationTable->setPrimaryKey(["id"]);

        $plasmidSequenceAnnotationTable = $schema->createTable("plasmid_sequence_annotation");
        $plasmidSequenceAnnotationTable->addColumn("substance_id", "guid")->setNotnull(true)->setComment("(DC2Type:ulid)");
        $plasmidSequenceAnnotationTable->addColumn("annotation_id", "guid")->setNotnull(true)->setComment("(DC2Type:ulid)");
        $plasmidSequenceAnnotationTable->setPrimaryKey(["substance_id", "annotation_id"]);

        $plasmidSequenceAnnotationTable->addIndex(["substance_id"], "IDX_1A4810EDC707E018");
        $plasmidSequenceAnnotationTable->addUniqueIndex(["annotation_id"], "UNIQ_1A4810EDE075FC54");

        $plasmidTable = $schema->getTable("plasmid");
        $plasmidTable->getColumn("growth_resistance")->setType(Type::getType("text"))->setComment("(DC2Type:simple_array)");
        $plasmidTable->getColumn("expression_resistance")->setType(Type::getType("text"))->setComment("(DC2Type:simple_array)");

        $plasmidSequenceAnnotationTable->addForeignKeyConstraint("plasmid", ["substance_id"], ["ulid"], ["onDelete" => "CASCADE"], "FK_1A4810EDC707E018");
        $plasmidSequenceAnnotationTable->addForeignKeyConstraint("sequence_annotation", ["annotation_id"], ["id"], ["onDelete" => "CASCADE"], "FK_1A4810EDE075FC54");
    }

    public function down(Schema $schema): void
    {
        $plasmidTable = $schema->getTable("plasmid");
        $plasmidTable->getColumn("growth_resistance")->setType(Type::getType("string"))->setComment(null)->setLength(255);
        $plasmidTable->getColumn("expression_resistance")->setType(Type::getType("string"))->setComment(null)->setLength(255);

        $table = $schema->getTable("plasmid_sequence_annotation");
        $table->removeForeignKey("FK_1A4810EDC707E018");
        $table->removeForeignKey("FK_1A4810EDE075FC54");

        $schema->dropTable("sequence_annotation");
        $schema->dropTable("plasmid_sequence_annotation");
    }
}
