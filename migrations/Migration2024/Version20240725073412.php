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

final class Version20240725073412 extends AbstractMigration
{
    public function __construct(Connection $connection, private readonly LoggerInterface $logger)
    {
        parent::__construct($connection, $logger);
    }

    public function getDescription(): string
    {
        return 'Add back primary key for other tables as well as foreign keys';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("cell");
        $table->addForeignKeyConstraint("cell", ["parent_id"], ["id"], ["onDelete" => "SET NULL"], "fk_cb8787e2727aca70");

        $table = $schema->getTable("cell_protein");
        $table->getColumn("cell_line_id")->setNotnull(true);
        $table->dropColumn("cell_line_ulid");
        $table->addForeignKeyConstraint("cell", ["cell_line_id"], ["id"], ["onDelete" => "CASCADE"], "FK_FD840B0528079FF5");

        $table = $schema->getTable("cell_aliquote");
        $table->getColumn("cell_id")->setNotnull(true);
        $table->dropColumn("cell_ulid");
        $table->addForeignKeyConstraint("cell", ["cell_id"], ["id"], options: ["onDelete" => "CASCADE"], name: "FK_E2BD6163CB39D93A");

        $table = $schema->getTable("cell_file");
        $table->getColumn("cell_id")->setNotnull(true);
        $table->dropColumn("cell_ulid");
        $table->setPrimaryKey(["cell_id", "file_id"]);
        $table->addForeignKeyConstraint("cell", ["cell_id"], ["id"], options: ["onDelete" => "CASCADE"], name: "FK_3F545FB6CB39D93A");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException("This migration is irreversible.");
    }
}
