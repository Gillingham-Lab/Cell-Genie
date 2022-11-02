<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Ulid;

final class Version20221101075021 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Data migration: Add new primary keys.';
    }

    public function up(Schema $schema): void
    {
        $schema->getTable("rack")->setPrimaryKey(["ulid"]);
        $schema->getTable("box")->setPrimaryKey(["ulid"]);
    }

    public function down(Schema $schema): void
    {

    }
}
