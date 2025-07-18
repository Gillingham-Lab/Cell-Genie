<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Psr\Log\LoggerInterface;

/**
 * Migrates old host entries to new epitope_hosts
 */
final class Version20220729094540 extends AbstractMigration
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
        $hosts = $this->connection->createQueryBuilder()
            ->select("ah.id, ah.name")
            ->from("antibody_host", "ah")
            ->fetchAllAssociative();

        foreach ($hosts as $host) {
            $this->connection->createQueryBuilder()->insert("epitope")
                ->setValue("id", ":id")
                ->setParameter("id", $host["id"], "ulid")
                ->setValue("short_name", ":name")
                ->setParameter("name", $host["name"])
                ->setValue("epitope_type", ":type")
                ->setParameter("type", "epitopehost")
                ->executeQuery();

            $this->connection->createQueryBuilder()->insert("epitope_host")
                ->setValue("id", ":id")
                ->setParameter("id", $host["id"], "ulid")
                ->executeQuery();

            $this->_logger->debug("Migrated antibody host {$host['name']} to epitopes");
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
