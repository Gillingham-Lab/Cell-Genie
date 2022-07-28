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
final class Version20220727061638 extends AbstractMigration
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
        # Drop all the old columns and indecies

        $table = $schema->getTable("antibody_protein");
        $table->dropIndex("idx_a4a1787654985755");
        $table->dropColumn("protein_id");
        $table->dropColumn("old_id");

        $table = $schema->getTable("experiment_protein");
        $table->dropIndex("idx_b6bb261854985755");
        $table->dropColumn("protein_id");
        $table->dropColumn("old_id");

        $table = $schema->getTable("experiment_chemical");
        $table->dropIndex("idx_b8f4e4f2e1770a76");
        $table->dropColumn("chemical_id");
        $table->dropColumn("old_id");

        $table = $schema->getTable("recipe_ingredient");
        $table->dropIndex("idx_22d1fe13e1770a76");
        $table->dropColumn("chemical_id");
        $table->dropColumn("old_id");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
