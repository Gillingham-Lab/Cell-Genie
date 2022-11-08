<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221101075025 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds max number of aliquots to lot.';
    }


    public function up(Schema $schema): void
    {
        $table = $schema->getTable("lot");
        $table->addColumn("max_number_of_aliquots", "smallint")->setNotnull(false);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("lot");
        $table->dropColumn("max_number_of_aliquots");
    }
}
