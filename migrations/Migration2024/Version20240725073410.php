<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Psr\Log\LoggerInterface;

final class Version20240725073410 extends AbstractMigration
{
    public function __construct(Connection $connection, private readonly LoggerInterface $logger)
    {
        parent::__construct($connection, $logger);
    }

    public function getDescription(): string
    {
        return 'Recreates the original ID columns.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("cell");
        $table->addColumn("id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");
        $table->addColumn("parent_id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");

        $table = $schema->getTable("cell_protein");
        $table->addColumn("cell_line_id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");

        $table = $schema->getTable("cell_aliquote");
        $table->addColumn("cell_id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");

        $table = $schema->getTable("cell_file");
        $table->addColumn("cell_id", Types::GUID)->setNotnull(false)->setComment("(DC2Type:ulid)");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException("This migration is irreversible.");
    }

    public function postUp(Schema $schema): void
    {
        $this->connection->createQueryBuilder()
            ->update("cell")
            ->set("id", "ulid")
            ->set("parent_id", "parent_ulid")
            ->executeStatement()
        ;

        $this->connection->createQueryBuilder()
            ->update("cell_protein")
            ->set("cell_line_id", "cell_line_ulid")
            ->executeStatement()
        ;

        $this->connection->createQueryBuilder()
            ->update("cell_aliquote")
            ->set("cell_id", "cell_ulid")
            ->executeStatement()
        ;

        $this->connection->createQueryBuilder()
            ->update("cell_file")
            ->set("cell_id", "cell_ulid")
            ->executeStatement()
        ;
    }
}
