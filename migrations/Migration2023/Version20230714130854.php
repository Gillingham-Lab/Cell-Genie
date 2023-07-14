<?php
declare(strict_types=1);

namespace DoctrineMigrations2023;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230714130854 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Makes cell names a non-unique index';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("cell");

        $table->dropIndex("uniq_cb8787e25e237e06");
        $table->addIndex(["name"], "IDX_CB8787E25E237E06");
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("cell");

        $table->dropIndex("IDX_CB8787E25E237E06");
        $table->addUniqueIndex(["name"], "uniq_cb8787e25e237e06");
    }
}
