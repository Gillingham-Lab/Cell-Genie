<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210324155638 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cell_aliquote (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, aliquoted_by_id BLOB DEFAULT NULL --(DC2Type:ulid)
        , box_id INTEGER NOT NULL, aliquoted_on DATETIME NOT NULL, vial_color VARCHAR(30) NOT NULL, vials INTEGER NOT NULL, passage INTEGER NOT NULL, cell_count INTEGER NOT NULL, mycoplasma CLOB DEFAULT NULL, typing CLOB DEFAULT NULL, history CLOB DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_E2BD6163C88E0642 ON cell_aliquote (aliquoted_by_id)');
        $this->addSql('CREATE INDEX IDX_E2BD6163D8177B3F ON cell_aliquote (box_id)');
        $this->addSql('DROP INDEX IDX_8A9483A8E86A33E');
        $this->addSql('CREATE TEMPORARY TABLE __temp__box AS SELECT id, rack_id, name, rows, cols FROM box');
        $this->addSql('DROP TABLE box');
        $this->addSql('CREATE TABLE box (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rack_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL COLLATE BINARY, rows INTEGER NOT NULL, cols INTEGER NOT NULL, CONSTRAINT FK_8A9483A8E86A33E FOREIGN KEY (rack_id) REFERENCES rack (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO box (id, rack_id, name, rows, cols) SELECT id, rack_id, name, rows, cols FROM __temp__box');
        $this->addSql('DROP TABLE __temp__box');
        $this->addSql('CREATE INDEX IDX_8A9483A8E86A33E ON box (rack_id)');
        $this->addSql('DROP INDEX IDX_520B4198D8177B3F');
        $this->addSql('CREATE TEMPORARY TABLE __temp__box_entry AS SELECT id, box_id, box_row, box_col FROM box_entry');
        $this->addSql('DROP TABLE box_entry');
        $this->addSql('CREATE TABLE box_entry (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, box_id INTEGER DEFAULT NULL, box_row INTEGER NOT NULL, box_col INTEGER NOT NULL, CONSTRAINT FK_520B4198D8177B3F FOREIGN KEY (box_id) REFERENCES box_entry (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO box_entry (id, box_id, box_row, box_col) SELECT id, box_id, box_row, box_col FROM __temp__box_entry');
        $this->addSql('DROP TABLE __temp__box_entry');
        $this->addSql('CREATE INDEX IDX_520B4198D8177B3F ON box_entry (box_id)');
        $this->addSql('DROP INDEX IDX_CB8787E2701EFC92');
        $this->addSql('DROP INDEX IDX_CB8787E264180A36');
        $this->addSql('DROP INDEX IDX_CB8787E2B38B33AD');
        $this->addSql('DROP INDEX IDX_CB8787E2727ACA70');
        $this->addSql('CREATE TEMPORARY TABLE __temp__cell AS SELECT id, parent_id, morphology_id, organism_id, tissue_id, name, age, culture_type, is_cancer, is_engineered FROM cell');
        $this->addSql('DROP TABLE cell');
        $this->addSql('CREATE TABLE cell (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, morphology_id INTEGER DEFAULT NULL, organism_id INTEGER DEFAULT NULL, tissue_id INTEGER DEFAULT NULL, parent_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL COLLATE BINARY, age VARCHAR(255) NOT NULL COLLATE BINARY, culture_type VARCHAR(255) NOT NULL COLLATE BINARY, is_cancer BOOLEAN NOT NULL, is_engineered BOOLEAN NOT NULL, CONSTRAINT FK_CB8787E2727ACA70 FOREIGN KEY (parent_id) REFERENCES cell (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CB8787E2B38B33AD FOREIGN KEY (morphology_id) REFERENCES morphology (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CB8787E264180A36 FOREIGN KEY (organism_id) REFERENCES organism (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CB8787E2701EFC92 FOREIGN KEY (tissue_id) REFERENCES tissue (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO cell (id, parent_id, morphology_id, organism_id, tissue_id, name, age, culture_type, is_cancer, is_engineered) SELECT id, parent_id, morphology_id, organism_id, tissue_id, name, age, culture_type, is_cancer, is_engineered FROM __temp__cell');
        $this->addSql('DROP TABLE __temp__cell');
        $this->addSql('CREATE INDEX IDX_CB8787E2701EFC92 ON cell (tissue_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E264180A36 ON cell (organism_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E2B38B33AD ON cell (morphology_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E2727ACA70 ON cell (parent_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE cell_aliquote');
        $this->addSql('DROP INDEX IDX_8A9483A8E86A33E');
        $this->addSql('CREATE TEMPORARY TABLE __temp__box AS SELECT id, rack_id, name, rows, cols FROM box');
        $this->addSql('DROP TABLE box');
        $this->addSql('CREATE TABLE box (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rack_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, rows INTEGER NOT NULL, cols INTEGER NOT NULL)');
        $this->addSql('INSERT INTO box (id, rack_id, name, rows, cols) SELECT id, rack_id, name, rows, cols FROM __temp__box');
        $this->addSql('DROP TABLE __temp__box');
        $this->addSql('CREATE INDEX IDX_8A9483A8E86A33E ON box (rack_id)');
        $this->addSql('DROP INDEX IDX_520B4198D8177B3F');
        $this->addSql('CREATE TEMPORARY TABLE __temp__box_entry AS SELECT id, box_id, box_row, box_col FROM box_entry');
        $this->addSql('DROP TABLE box_entry');
        $this->addSql('CREATE TABLE box_entry (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, box_id INTEGER DEFAULT NULL, box_row INTEGER NOT NULL, box_col INTEGER NOT NULL)');
        $this->addSql('INSERT INTO box_entry (id, box_id, box_row, box_col) SELECT id, box_id, box_row, box_col FROM __temp__box_entry');
        $this->addSql('DROP TABLE __temp__box_entry');
        $this->addSql('CREATE INDEX IDX_520B4198D8177B3F ON box_entry (box_id)');
        $this->addSql('DROP INDEX IDX_CB8787E2727ACA70');
        $this->addSql('DROP INDEX IDX_CB8787E2B38B33AD');
        $this->addSql('DROP INDEX IDX_CB8787E264180A36');
        $this->addSql('DROP INDEX IDX_CB8787E2701EFC92');
        $this->addSql('CREATE TEMPORARY TABLE __temp__cell AS SELECT id, parent_id, morphology_id, organism_id, tissue_id, name, age, culture_type, is_cancer, is_engineered FROM cell');
        $this->addSql('DROP TABLE cell');
        $this->addSql('CREATE TABLE cell (id BLOB NOT NULL --(DC2Type:ulid)
        , morphology_id INTEGER DEFAULT NULL, organism_id INTEGER DEFAULT NULL, tissue_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, age VARCHAR(255) NOT NULL, culture_type VARCHAR(255) NOT NULL, is_cancer BOOLEAN NOT NULL, is_engineered BOOLEAN NOT NULL, parent_id BLOB DEFAULT NULL --(DC2Type:ulid)
        , PRIMARY KEY(id))');
        $this->addSql('INSERT INTO cell (id, parent_id, morphology_id, organism_id, tissue_id, name, age, culture_type, is_cancer, is_engineered) SELECT id, parent_id, morphology_id, organism_id, tissue_id, name, age, culture_type, is_cancer, is_engineered FROM __temp__cell');
        $this->addSql('DROP TABLE __temp__cell');
        $this->addSql('CREATE INDEX IDX_CB8787E2727ACA70 ON cell (parent_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E2B38B33AD ON cell (morphology_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E264180A36 ON cell (organism_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E2701EFC92 ON cell (tissue_id)');
    }
}
