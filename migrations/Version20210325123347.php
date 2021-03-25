<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210325123347 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_8A9483A8E86A33E');
        $this->addSql('CREATE TEMPORARY TABLE __temp__box AS SELECT id, rack_id, name, rows, cols FROM box');
        $this->addSql('DROP TABLE box');
        $this->addSql('CREATE TABLE box (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rack_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL COLLATE BINARY, rows INTEGER NOT NULL, cols INTEGER NOT NULL, CONSTRAINT FK_8A9483A8E86A33E FOREIGN KEY (rack_id) REFERENCES rack (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO box (id, rack_id, name, rows, cols) SELECT id, rack_id, name, rows, cols FROM __temp__box');
        $this->addSql('DROP TABLE __temp__box');
        $this->addSql('CREATE INDEX IDX_8A9483A8E86A33E ON box (rack_id)');
        $this->addSql('DROP INDEX IDX_CB8787E2727ACA70');
        $this->addSql('DROP INDEX IDX_CB8787E2B38B33AD');
        $this->addSql('DROP INDEX IDX_CB8787E264180A36');
        $this->addSql('DROP INDEX IDX_CB8787E2701EFC92');
        $this->addSql('DROP INDEX UNIQ_CB8787E25E237E06');
        $this->addSql('DROP INDEX IDX_CB8787E2DEC6D6BA');
        $this->addSql('CREATE TEMPORARY TABLE __temp__cell AS SELECT id, morphology_id, organism_id, tissue_id, parent_id, bought_by_id, name, age, culture_type, is_cancer, is_engineered, origin, vendor, vendor_id, price, origin_comment, acquired_on FROM cell');
        $this->addSql('DROP TABLE cell');
        $this->addSql('CREATE TABLE cell (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, morphology_id INTEGER DEFAULT NULL, organism_id INTEGER DEFAULT NULL, tissue_id INTEGER DEFAULT NULL, parent_id INTEGER DEFAULT NULL, bought_by_id BLOB DEFAULT NULL --(DC2Type:ulid)
        , name VARCHAR(255) NOT NULL COLLATE BINARY, age VARCHAR(255) NOT NULL COLLATE BINARY, culture_type VARCHAR(255) NOT NULL COLLATE BINARY, is_cancer BOOLEAN NOT NULL, is_engineered BOOLEAN NOT NULL, origin VARCHAR(255) DEFAULT NULL COLLATE BINARY, vendor VARCHAR(255) DEFAULT NULL COLLATE BINARY, vendor_id VARCHAR(255) DEFAULT NULL COLLATE BINARY, price NUMERIC(7, 2) DEFAULT NULL, origin_comment CLOB DEFAULT NULL COLLATE BINARY, acquired_on DATETIME DEFAULT NULL, medium VARCHAR(255) DEFAULT NULL, freezing CLOB DEFAULT NULL, thawing CLOB DEFAULT NULL, culture_conditions CLOB DEFAULT NULL, CONSTRAINT FK_CB8787E2727ACA70 FOREIGN KEY (parent_id) REFERENCES cell (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CB8787E2B38B33AD FOREIGN KEY (morphology_id) REFERENCES morphology (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CB8787E264180A36 FOREIGN KEY (organism_id) REFERENCES organism (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CB8787E2701EFC92 FOREIGN KEY (tissue_id) REFERENCES tissue (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CB8787E2DEC6D6BA FOREIGN KEY (bought_by_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO cell (id, morphology_id, organism_id, tissue_id, parent_id, bought_by_id, name, age, culture_type, is_cancer, is_engineered, origin, vendor, vendor_id, price, origin_comment, acquired_on) SELECT id, morphology_id, organism_id, tissue_id, parent_id, bought_by_id, name, age, culture_type, is_cancer, is_engineered, origin, vendor, vendor_id, price, origin_comment, acquired_on FROM __temp__cell');
        $this->addSql('DROP TABLE __temp__cell');
        $this->addSql('CREATE INDEX IDX_CB8787E2727ACA70 ON cell (parent_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E2B38B33AD ON cell (morphology_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E264180A36 ON cell (organism_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E2701EFC92 ON cell (tissue_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CB8787E25E237E06 ON cell (name)');
        $this->addSql('CREATE INDEX IDX_CB8787E2DEC6D6BA ON cell (bought_by_id)');
        $this->addSql('DROP INDEX IDX_E2BD6163D8177B3F');
        $this->addSql('DROP INDEX IDX_E2BD6163C88E0642');
        $this->addSql('DROP INDEX IDX_E2BD6163CB39D93A');
        $this->addSql('CREATE TEMPORARY TABLE __temp__cell_aliquote AS SELECT id, aliquoted_by_id, box_id, cell_id, aliquoted_on, vial_color, vials, passage, cell_count, mycoplasma, typing, history FROM cell_aliquote');
        $this->addSql('DROP TABLE cell_aliquote');
        $this->addSql('CREATE TABLE cell_aliquote (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, aliquoted_by_id BLOB DEFAULT NULL --(DC2Type:ulid)
        , box_id INTEGER NOT NULL, cell_id INTEGER NOT NULL, aliquoted_on DATETIME NOT NULL, vial_color VARCHAR(30) NOT NULL COLLATE BINARY, vials INTEGER NOT NULL, passage INTEGER NOT NULL, cell_count INTEGER NOT NULL, mycoplasma CLOB DEFAULT NULL COLLATE BINARY, typing CLOB DEFAULT NULL COLLATE BINARY, history CLOB DEFAULT NULL COLLATE BINARY, CONSTRAINT FK_E2BD6163C88E0642 FOREIGN KEY (aliquoted_by_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_E2BD6163D8177B3F FOREIGN KEY (box_id) REFERENCES box (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_E2BD6163CB39D93A FOREIGN KEY (cell_id) REFERENCES cell (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO cell_aliquote (id, aliquoted_by_id, box_id, cell_id, aliquoted_on, vial_color, vials, passage, cell_count, mycoplasma, typing, history) SELECT id, aliquoted_by_id, box_id, cell_id, aliquoted_on, vial_color, vials, passage, cell_count, mycoplasma, typing, history FROM __temp__cell_aliquote');
        $this->addSql('DROP TABLE __temp__cell_aliquote');
        $this->addSql('CREATE INDEX IDX_E2BD6163D8177B3F ON cell_aliquote (box_id)');
        $this->addSql('CREATE INDEX IDX_E2BD6163C88E0642 ON cell_aliquote (aliquoted_by_id)');
        $this->addSql('CREATE INDEX IDX_E2BD6163CB39D93A ON cell_aliquote (cell_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_8A9483A8E86A33E');
        $this->addSql('CREATE TEMPORARY TABLE __temp__box AS SELECT id, rack_id, name, rows, cols FROM box');
        $this->addSql('DROP TABLE box');
        $this->addSql('CREATE TABLE box (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rack_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, rows INTEGER NOT NULL, cols INTEGER NOT NULL)');
        $this->addSql('INSERT INTO box (id, rack_id, name, rows, cols) SELECT id, rack_id, name, rows, cols FROM __temp__box');
        $this->addSql('DROP TABLE __temp__box');
        $this->addSql('CREATE INDEX IDX_8A9483A8E86A33E ON box (rack_id)');
        $this->addSql('DROP INDEX UNIQ_CB8787E25E237E06');
        $this->addSql('DROP INDEX IDX_CB8787E2727ACA70');
        $this->addSql('DROP INDEX IDX_CB8787E2B38B33AD');
        $this->addSql('DROP INDEX IDX_CB8787E264180A36');
        $this->addSql('DROP INDEX IDX_CB8787E2701EFC92');
        $this->addSql('DROP INDEX IDX_CB8787E2DEC6D6BA');
        $this->addSql('CREATE TEMPORARY TABLE __temp__cell AS SELECT id, parent_id, morphology_id, organism_id, tissue_id, bought_by_id, name, age, culture_type, is_cancer, is_engineered, origin, vendor, vendor_id, acquired_on, price, origin_comment FROM cell');
        $this->addSql('DROP TABLE cell');
        $this->addSql('CREATE TABLE cell (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, parent_id INTEGER DEFAULT NULL, morphology_id INTEGER DEFAULT NULL, organism_id INTEGER DEFAULT NULL, tissue_id INTEGER DEFAULT NULL, bought_by_id BLOB DEFAULT NULL --(DC2Type:ulid)
        , name VARCHAR(255) NOT NULL, age VARCHAR(255) NOT NULL, culture_type VARCHAR(255) NOT NULL, is_cancer BOOLEAN NOT NULL, is_engineered BOOLEAN NOT NULL, origin VARCHAR(255) DEFAULT NULL, vendor VARCHAR(255) DEFAULT NULL, vendor_id VARCHAR(255) DEFAULT NULL, acquired_on DATETIME DEFAULT NULL, price NUMERIC(7, 2) DEFAULT NULL, origin_comment CLOB DEFAULT NULL)');
        $this->addSql('INSERT INTO cell (id, parent_id, morphology_id, organism_id, tissue_id, bought_by_id, name, age, culture_type, is_cancer, is_engineered, origin, vendor, vendor_id, acquired_on, price, origin_comment) SELECT id, parent_id, morphology_id, organism_id, tissue_id, bought_by_id, name, age, culture_type, is_cancer, is_engineered, origin, vendor, vendor_id, acquired_on, price, origin_comment FROM __temp__cell');
        $this->addSql('DROP TABLE __temp__cell');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CB8787E25E237E06 ON cell (name)');
        $this->addSql('CREATE INDEX IDX_CB8787E2727ACA70 ON cell (parent_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E2B38B33AD ON cell (morphology_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E264180A36 ON cell (organism_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E2701EFC92 ON cell (tissue_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E2DEC6D6BA ON cell (bought_by_id)');
        $this->addSql('DROP INDEX IDX_E2BD6163C88E0642');
        $this->addSql('DROP INDEX IDX_E2BD6163D8177B3F');
        $this->addSql('DROP INDEX IDX_E2BD6163CB39D93A');
        $this->addSql('CREATE TEMPORARY TABLE __temp__cell_aliquote AS SELECT id, aliquoted_by_id, box_id, cell_id, aliquoted_on, vial_color, vials, passage, cell_count, mycoplasma, typing, history FROM cell_aliquote');
        $this->addSql('DROP TABLE cell_aliquote');
        $this->addSql('CREATE TABLE cell_aliquote (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, aliquoted_by_id BLOB DEFAULT NULL --(DC2Type:ulid)
        , box_id INTEGER NOT NULL, cell_id INTEGER NOT NULL, aliquoted_on DATETIME NOT NULL, vial_color VARCHAR(30) NOT NULL, vials INTEGER NOT NULL, passage INTEGER NOT NULL, cell_count INTEGER NOT NULL, mycoplasma CLOB DEFAULT NULL, typing CLOB DEFAULT NULL, history CLOB DEFAULT NULL)');
        $this->addSql('INSERT INTO cell_aliquote (id, aliquoted_by_id, box_id, cell_id, aliquoted_on, vial_color, vials, passage, cell_count, mycoplasma, typing, history) SELECT id, aliquoted_by_id, box_id, cell_id, aliquoted_on, vial_color, vials, passage, cell_count, mycoplasma, typing, history FROM __temp__cell_aliquote');
        $this->addSql('DROP TABLE __temp__cell_aliquote');
        $this->addSql('CREATE INDEX IDX_E2BD6163C88E0642 ON cell_aliquote (aliquoted_by_id)');
        $this->addSql('CREATE INDEX IDX_E2BD6163D8177B3F ON cell_aliquote (box_id)');
        $this->addSql('CREATE INDEX IDX_E2BD6163CB39D93A ON cell_aliquote (cell_id)');
    }
}
