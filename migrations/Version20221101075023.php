<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Ulid;

final class Version20221101075023 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Enables racks to have a parent';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("rack");
        $table->addColumn("parent_id", "guid")->setNotnull(false)->setComment("(DC2Type:ulid)");
        $table->addForeignKeyConstraint("rack", ["parent_id"], ["ulid"], ["onDelete" => "SET NULL"], "FK_3DD796A8727ACA70");
        $table->addIndex(["parent_id"], "IDX_3DD796A8727ACA70");
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("rack");
        $table->removeForeignKey("FK_3DD796A8727ACA70");
        $table->dropIndex("IDX_3DD796A8727ACA70");
        $table->dropColumn("parent_id");
    }
}
