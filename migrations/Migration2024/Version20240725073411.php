<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Service\Doctrine\Type\Ulid;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Psr\Log\LoggerInterface;

final class Version20240725073411 extends AbstractMigration
{
    public function __construct(Connection $connection, private readonly LoggerInterface $logger)
    {
        parent::__construct($connection, $logger);
    }

    public function getDescription(): string
    {
        return 'Add back primary key for cell table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("cell");
        $table->getColumn("id")->setNotnull(true);
        $table->dropColumn("ulid");
        $table->dropColumn("parent_id_ulid");
        $table->setPrimaryKey(["id"]);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException("This migration is irreversible.");
    }
}
