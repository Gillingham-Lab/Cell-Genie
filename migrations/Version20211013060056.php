<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211013060056 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("experimental_run");
        $table->addColumn("data", "array")->setDefault("a:0:{}")->setNotnull(true);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("experimental_run");
        $table->dropColumn("data");
    }
}
