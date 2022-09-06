<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Psr\Log\LoggerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220905142847 extends AbstractMigration
{
    public function __construct(Connection $connection, LoggerInterface $logger)
    {
        $this->_logger = $logger;
        parent::__construct($connection, $logger);
    }

    public function getDescription(): string
    {
        return 'Adds FK relationships from substances to new children tables.';
    }

    public function up(Schema $schema): void
    {
        $schema->getTable("antibody")
            ->addForeignKeyConstraint("substance", ["ulid"], ["ulid"], ["onDelete" => "CASCADE"], "FK_5C97C6B1C288C859");

        $schema->getTable("chemical")
            ->addForeignKeyConstraint("substance", ["ulid"], ["ulid"], ["onDelete" => "CASCADE"], "FK_8ED9EDC3C288C859");

        $schema->getTable("protein")
            ->addForeignKeyConstraint("substance", ["ulid"], ["ulid"], ["onDelete" => "CASCADE"], "FK_98F8E1B2C288C859");
    }

    public function down(Schema $schema): void
    {
        $schema->getTable("antibody")->removeForeignKey("FK_5C97C6B1C288C859");
        $schema->getTable("chemical")->removeForeignKey("FK_8ED9EDC3C288C859");
        $schema->getTable("protein")->removeForeignKey("FK_98F8E1B2C288C859");
    }
}
