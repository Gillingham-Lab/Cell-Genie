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

final class Version20240725073409 extends AbstractMigration
{
    public function __construct(Connection $connection, private readonly LoggerInterface $logger)
    {
        parent::__construct($connection, $logger);
    }

    public function getDescription(): string
    {
        return 'Removes foreign key constraints from cell id tables and removes the original id columns';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("cell");
        $table->removeForeignKey("fk_cb8787e2727aca70");
        $table->dropColumn("id");
        $table->dropColumn("parent_id");

        $table = $schema->getTable("cell_protein");
        $table->removeForeignKey("fk_fd840b0528079ff5");
        $table->dropColumn("cell_line_id");

        $table = $schema->getTable("cell_aliquote");
        $table->removeForeignKey("fk_e2bd6163cb39d93a");
        $table->dropColumn("cell_id");

        $table = $schema->getTable("cell_file");
        $table->removeForeignKey("fk_3f545fb6cb39d93a");
        $table->dropColumn("cell_id");

        $table = $schema->getTable("experiment_cell");
        $table->removeForeignKey("fk_d078464fcb39d93a");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException("This migration is irreversible.");
    }
}
