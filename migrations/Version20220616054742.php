<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220616054742 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable("cell");
        $table->addColumn("engineer_id", "guid")
            ->setNotnull(false)
            ->setDefault(null)
            ->setComment("(DC2Type:ulid)");
        $table->addColumn("engineering_plasmid", "string")
            ->setNotnull(false)
            ->setDefault(null);

        $table->addIndex(["engineer_id"], "IDX_CB8787E2F8D8CDF1");
        $table->addForeignKeyConstraint(
            "user_accounts",
            ["engineer_id"],
            ["id"],
            ["onDelete" => "SET NULL"],
            "FK_CB8787E2F8D8CDF1"
        );
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("cell");
        $table->removeForeignKey("FK_CB8787E2F8D8CDF1");
        $table->dropIndex("IDX_CB8787E2F8D8CDF1");
        $table->dropColumn("engineer_id");
        $table->dropColumn("engineering_plasmid");
    }
}
