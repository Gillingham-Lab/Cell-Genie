<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Ulid;

final class Version20221101075020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Data migration: Remove primary keys and foreign key constraints.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE rack DROP CONSTRAINT {$schema->getTable("rack")->getPrimaryKey()->getName()} CASCADE");
        $this->addSql("ALTER TABLE box DROP CONSTRAINT {$schema->getTable("box")->getPrimaryKey()->getName()} CASCADE");
    }

    public function down(Schema $schema): void
    {

    }
}
