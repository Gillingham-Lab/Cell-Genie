<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220616123707 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable("cell_aliquote");

        $table->addColumn("passage_detail", "string")
            ->setLength(255)
            ->setNotnull(false)
            ->setDefault(null);

        $table->addColumn("mycoplasma_tested_by_id", "guid")
            ->setNotnull(false)
            ->setDefault(null)
            ->setComment("DC2Type:ulid)");

        $table->addColumn("mycoplasma_tested_on", "datetime")
            ->setNotnull(false)
            ->setDefault(null);

        $table->addColumn("mycoplasma_result", "string")
            ->setLength(255)
            ->setNotnull(true)
            ->setDefault("unknown");

        $table->getColumn("aliquoted_on")->setNotnull(false)->setDefault(null);

        $table->addForeignKeyConstraint("user_accounts", ["mycoplasma_tested_by_id"], ["id"], ["onDelete" => "SET NULL"], "FK_E2BD616320E05D61");
        $table->addIndex(["mycoplasma_tested_by_id"], "IDX_E2BD616320E05D61");
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("cell_aliquote");

        $table->getColumn("aliquoted_on")->setNotnull(true)->setDefault("1970-01-02 00:00:00");

        $table->dropIndex("IDX_E2BD616320E05D61");
        $table->removeForeignKey("FK_E2BD616320E05D61");

        $table->dropColumn("mycoplasma_tested_by_id");
        $table->dropColumn("passage_detail");
        $table->dropColumn("mycoplasma_tested_on");
        $table->dropColumn("mycoplasma_result");
    }
}
