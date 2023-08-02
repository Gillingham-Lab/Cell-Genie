<?php
declare(strict_types=1);

namespace DoctrineMigrations2023;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20230802093701 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a citation text field to the instrument table.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("instrument");
        $table->addColumn("citation_text", Types::TEXT)->setNotnull(false);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("instrument");
        $table->dropColumn("citation_text");
    }
}
