<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221017132444 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'More cleanup';
    }

    public function up(Schema $schema): void
    {
        $schema->dropTable("epitope_protein");

        $this->addSql("ALTER TABLE \"substance_epitopes\" DROP CONSTRAINT \"substance_epitopes_pkey\"");
        $this->addSql("ALTER TABLE \"substance_epitopes\" ADD PRIMARY KEY (\"epitope_id\", \"substance_ulid\")");

        $this->addSql("ALTER TABLE ext_log_entries ALTER id DROP DEFAULT");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
