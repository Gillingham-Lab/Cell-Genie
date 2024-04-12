<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20240412104018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds type and conjugation fields to oligos.';
    }

    public function up(Schema $schema): void
    {
        $oligoTable = $schema->getTable("oligo");
        $oligoTable->addColumn("start_conjugate_id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");
        $oligoTable->addColumn("end_conjugate_id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");
        $oligoTable->addColumn("oligo_type_enum", Types::STRING)->setNotnull(false)->setLength(255);

        $oligoTable->addForeignKeyConstraint("substance", ["start_conjugate_id"], ["ulid"], ["onDelete" => "SET NULL"], "FK_1C072E3CDDA34969");
        $oligoTable->addForeignKeyConstraint("substance", ["end_conjugate_id"], ["ulid"], ["onDelete" => "SET NULL"], "FK_1C072E3CD1452BA7");

        $oligoTable->addIndex(["start_conjugate_id"], "IDX_1C072E3CDDA34969");
        $oligoTable->addIndex(["end_conjugate_id"], "IDX_1C072E3CD1452BA7");
    }

    public function down(Schema $schema): void
    {
        $oligoTable = $schema->getTable("oligo");

        $oligoTable->removeForeignKey("FK_1C072E3CDDA34969");
        $oligoTable->removeForeignKey("FK_1C072E3CD1452BA7");
        $oligoTable->dropIndex("IDX_1C072E3CDDA34969");
        $oligoTable->dropIndex("IDX_1C072E3CD1452BA7");

        $oligoTable->dropColumn("start_conjugate_id");
        $oligoTable->dropColumn("end_conjugate_id");
        $oligoTable->dropColumn("oligo_type_enum");
    }
}
