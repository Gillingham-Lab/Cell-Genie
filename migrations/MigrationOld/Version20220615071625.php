<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220615071625 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("cell")
            ->addColumn("engineering_description", "text")->setNotnull(false)->setDefault(null);
    }

    public function down(Schema $schema): void
    {
        $schema->getTable("cell")->dropColumn("engineering_description");
    }
}
