<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240426122435 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds an index to the virtual uuid column';
    }

    public function up(Schema $schema): void
    {
        $schema->getTable("new_experimental_datum")
            ->addIndex(["reference_uuid"], "IDX_D435347BA6BCA806");
    }

    public function down(Schema $schema): void
    {
        $schema->getTable("new_experimental_datum")->dropIndex("IDX_D435347BA6BCA806");
    }
}
