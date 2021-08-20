<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210820124937 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("lot");
        $table->removeForeignKey("FK_B81291BD8177B3F");
        $table->addForeignKeyConstraint("box", ["box_id"], ["id"], ["onDelete" => "SET NULL"], "FK_B81291BD8177B3F");
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("lot");
        $table->removeForeignKey("FK_B81291BD8177B3F");
        $table->addForeignKeyConstraint("box", ["box_id"], ["id"], [], "FK_B81291BD8177B3F");
    }
}
