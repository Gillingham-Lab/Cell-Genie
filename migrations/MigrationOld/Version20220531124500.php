<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220531124500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("cell");
        $table->addColumn("sex", typeName: "string")
            ->setLength(50)
            ->setNotnull(false);
        $table->addColumn("ethnicity", typeName: "string")
            ->setLength(50)
            ->setNotnull(false);
        $table->addColumn("disease", typeName: "string")
            ->setLength(255)
            ->setNotnull(false);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("cell");
        $table->dropColumn("sex");
        $table->dropColumn("ethnicity");
        $table->dropColumn("disease");
    }
}
