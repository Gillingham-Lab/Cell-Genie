<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220211084008 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("recipe");
        $table->addColumn("category", typeName: "string")
            ->setLength(100)
            ->setNotnull(false);

        $table = $schema->getTable("recipe_ingredient");
        $table->dropIndex("IDX_22D1FE1359D8A214");
        $table->dropIndex("IDX_22D1FE13E1770A76");
        $table->addIndex(["recipe_id"], indexName: "IDX_22D1FE1359D8A214");
        $table->addIndex(["chemical_id"], indexName: "IDX_22D1FE13E1770A76");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
