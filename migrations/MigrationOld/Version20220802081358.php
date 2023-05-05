<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Psr\Log\LoggerInterface;

final class Version20220802081358 extends AbstractMigration
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
        // Remove foreign constraints
        $schema->getTable("antibody_lots")->removeForeignKey("FK_5c96db8651162764");
        $schema->getTable("antibody_vendor_documentation_files")->removeForeignKey("fk_5b63125d51162764");
        $schema->getTable("antibody_protein")->removeForeignKey("fk_a4a1787651162764");

        // Add missing antibody_ulid to vendor_doc_files
        // I'm not doing this for antibody_protein, as this relationship will be expressed differently later anyway
        $schema->getTable("antibody_vendor_documentation_files")
            ->addColumn("antibody_ulid", "guid")->setNotnull(false)->setComment("(DC2Type:ulid)");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
