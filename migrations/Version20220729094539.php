<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220729094539 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("recipe_ingredient");
        $table->setPrimaryKey(["id"]);
    }

    public function down(Schema $schema): void
    {
       $this->throwIrreversibleMigrationException();
    }
}
