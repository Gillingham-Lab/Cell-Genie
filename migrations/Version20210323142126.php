<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210323142126 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE box (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rack_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, rows INTEGER NOT NULL, cols INTEGER NOT NULL)');
        $this->addSql('CREATE INDEX IDX_8A9483A8E86A33E ON box (rack_id)');
        $this->addSql('CREATE TABLE box_entry (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, box_id INTEGER DEFAULT NULL, box_row INTEGER NOT NULL, box_col INTEGER NOT NULL)');
        $this->addSql('CREATE INDEX IDX_520B4198D8177B3F ON box_entry (box_id)');
        $this->addSql('CREATE TABLE cell (id BLOB NOT NULL --(DC2Type:ulid)
        , parent_id BLOB DEFAULT NULL --(DC2Type:ulid)
        , morphology_id INTEGER DEFAULT NULL, organism_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, age VARCHAR(255) NOT NULL, culture_type VARCHAR(255) NOT NULL, is_cancer BOOLEAN NOT NULL, is_engineered BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CB8787E2727ACA70 ON cell (parent_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E2B38B33AD ON cell (morphology_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E264180A36 ON cell (organism_id)');
        $this->addSql('CREATE TABLE morphology (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE organism (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE rack (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, max_boxes INTEGER NOT NULL)');
        $this->addSql('CREATE TABLE tissue (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE box');
        $this->addSql('DROP TABLE box_entry');
        $this->addSql('DROP TABLE cell');
        $this->addSql('DROP TABLE morphology');
        $this->addSql('DROP TABLE organism');
        $this->addSql('DROP TABLE rack');
        $this->addSql('DROP TABLE tissue');
    }
}
