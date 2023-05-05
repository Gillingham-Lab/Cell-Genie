<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210818081602 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        //$this->addSql('ALTER TABLE antibody ADD host_organism_id UUID DEFAULT NULL');
        //$this->addSql('ALTER TABLE antibody ADD host_target_id UUID DEFAULT NULL');
        //$this->addSql('COMMENT ON COLUMN antibody.host_organism_id IS \'(DC2Type:ulid)\'');
        //$this->addSql('COMMENT ON COLUMN antibody.host_target_id IS \'(DC2Type:ulid)\'');
        $table = $schema->getTable("antibody");
        $table->addColumn("host_organism_id", "guid")
            ->setNotnull(false)
            ->setComment("(DC2Type:ulid)");
        $table->addColumn("host_target_id", "guid")
            ->setNotnull(false)
            ->setComment("(DC2Type:ulid)");

        //$this->addSql('ALTER TABLE antibody ADD CONSTRAINT FK_5C97C6B11D6C21C8 FOREIGN KEY (host_organism_id) REFERENCES antibody_host (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        //$this->addSql('ALTER TABLE antibody ADD CONSTRAINT FK_5C97C6B1E10B57CB FOREIGN KEY (host_target_id) REFERENCES antibody_host (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        //$this->addSql('CREATE INDEX IDX_5C97C6B11D6C21C8 ON antibody (host_organism_id)');
        //$this->addSql('CREATE INDEX IDX_5C97C6B1E10B57CB ON antibody (host_target_id)');
        $table->addForeignKeyConstraint("antibody_host", ["host_organism_id"], ["id"], name: "FK_5C97C6B11D6C21C8");
        $table->addForeignKeyConstraint("antibody_host", ["host_target_id"], ["id"], name: "FK_5C97C6B1E10B57CB");
        $table->addIndex(["host_organism_id"], "FK_5C97C6B11D6C21C8");
        $table->addIndex(["host_target_id"], "FK_5C97C6B1E10B57CB");
    }

    public function down(Schema $schema): void
    {
        //$this->addSql('ALTER TABLE antibody DROP CONSTRAINT FK_5C97C6B11D6C21C8');
        //$this->addSql('ALTER TABLE antibody DROP CONSTRAINT FK_5C97C6B1E10B57CB');
        //$this->addSql('DROP INDEX IDX_5C97C6B11D6C21C8');
        //$this->addSql('DROP INDEX IDX_5C97C6B1E10B57CB');
        $table = $schema->getTable("antibody");
        $table->removeForeignKey("FK_5C97C6B11D6C21C8");
        $table->removeForeignKey("FK_5C97C6B1E10B57CB");
        $table->dropIndex("FK_5C97C6B11D6C21C8");
        $table->dropIndex("FK_5C97C6B1E10B57CB");

        //$this->addSql('ALTER TABLE antibody DROP host_organism_id');
        //$this->addSql('ALTER TABLE antibody DROP host_target_id');
        $table->dropColumn("host_organism_id");
        $table->dropColumn("host_target_id");
    }
}
