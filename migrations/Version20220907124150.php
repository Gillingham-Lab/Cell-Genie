<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220907124150 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds vendor fields to lot table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("lot");
        $table->addColumn("vendor_id", "integer")->setNotnull(false)->setDefault(null);
        $table->addColumn("vendor_pn", "string")->setLength(255)->setNotnull(false)->setDefault(null);

        $table->addForeignKeyConstraint("vendor", ["vendor_id"], ["id"], ["onDelete" => "SET NULL"], "FK_B81291BF603EE73");
        $table->addIndex(["vendor_id"], "IDX_B81291BF603EE73");
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("lot");

        $table->removeForeignKey("FK_B81291BF603EE73");
        $table->dropIndex("IDX_B81291BF603EE73");

        $table->dropColumn("vendor_id");
        $table->dropColumn("vendor_pn");
    }
}
