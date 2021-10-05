<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Exception\IrreversibleMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210923082501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        /*
        $this->addSql('DROP SEQUENCE experiment_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE experiment_type_id_seq CASCADE');
        */
        $schema->dropSequence("experiment_type_id_seq");
        $schema->dropSequence("experiment_id_seq");

        $schema->dropTable("antibody_dilution");
        $schema->dropTable("experiment");
        $schema->dropTable("experiment_protein");
        $schema->dropTable("experiment_chemical");
        $schema->dropTable("experiment_cell");
        $schema->dropTable("experiment_type");
    }

    public function down(Schema $schema): void
    {
        throw new IrreversibleMigration();
    }
}
