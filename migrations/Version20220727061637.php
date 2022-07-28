<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Ulid;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220727061637 extends AbstractMigration
{
    public function __construct(Connection $connection, LoggerInterface $logger)
    {
        $this->_logger = $logger;
        parent::__construct($connection, $logger);
    }

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        # Create new primary keys
        $table = $schema->getTable("antibody_protein");
        $table->setPrimaryKey(["antibody_id", "protein_ulid"]);

        $table = $schema->getTable("experiment_protein");
        $table->setPrimaryKey(["experiment_id", "protein_ulid"]);

        $table = $schema->getTable("experiment_chemical");
        $table->setPrimaryKey(["experiment_id", "chemical_ulid"]);

        $table = $schema->getTable("recipe_ingredient");
        $table->setPrimaryKey(["id", "chemical_ulid"]);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
