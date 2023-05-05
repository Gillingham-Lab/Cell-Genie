<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210819065021 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Bugfix - does not make stuff incompatible though!
        $table = $schema->getTable("antibody_host");
        $table->getColumn("id")
            ->setComment("(DC2Type:ulid)");
        $table->getColumn("name")
            ->setComment(null);

        // Renaming indecies.
        $table = $schema->getTable("antibody");
        $table->renameIndex("fk_5c97c6b11d6c21c8", "IDX_5C97C6B11D6C21C8");
        $table->renameIndex("fk_5c97c6b1e10b57cb", "IDX_5C97C6B1E10B57CB");
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("antibody");
        $table->renameIndex("IDX_5C97C6B11D6C21C8", "fk_5c97c6b11d6c21c8");
        $table->renameIndex("IDX_5C97C6B1E10B57CB", "fk_5c97c6b1e10b57cb");
    }
}
