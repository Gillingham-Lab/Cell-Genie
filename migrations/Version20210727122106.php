<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210727122106 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_5C97C6B1F603EE73');
        $this->addSql('CREATE TEMPORARY TABLE __temp__antibody AS SELECT id, vendor_id, short_name, long_name, vendor_pn, detection, number FROM antibody');
        $this->addSql('DROP TABLE antibody');
        $this->addSql('CREATE TABLE antibody (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, vendor_id INTEGER DEFAULT NULL, short_name VARCHAR(255) NOT NULL COLLATE BINARY, long_name VARCHAR(255) NOT NULL COLLATE BINARY, vendor_pn VARCHAR(50) DEFAULT NULL COLLATE BINARY, detection VARCHAR(255) DEFAULT NULL COLLATE BINARY, number VARCHAR(10) DEFAULT NULL COLLATE BINARY, validated_internally BOOLEAN DEFAULT \'0\' NOT NULL, validated_externally BOOLEAN DEFAULT \'0\' NOT NULL, external_reference VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_5C97C6B1F603EE73 FOREIGN KEY (vendor_id) REFERENCES vendor (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO antibody (id, vendor_id, short_name, long_name, vendor_pn, detection, number) SELECT id, vendor_id, short_name, long_name, vendor_pn, detection, number FROM __temp__antibody');
        $this->addSql('DROP TABLE __temp__antibody');
        $this->addSql('CREATE INDEX IDX_5C97C6B1F603EE73 ON antibody (vendor_id)');
        $this->addSql('DROP INDEX IDX_A4A1787654985755');
        $this->addSql('DROP INDEX IDX_A4A1787651162764');
        $this->addSql('CREATE TEMPORARY TABLE __temp__antibody_protein AS SELECT antibody_id, protein_id FROM antibody_protein');
        $this->addSql('DROP TABLE antibody_protein');
        $this->addSql('CREATE TABLE antibody_protein (antibody_id INTEGER NOT NULL, protein_id INTEGER NOT NULL, PRIMARY KEY(antibody_id, protein_id), CONSTRAINT FK_A4A1787651162764 FOREIGN KEY (antibody_id) REFERENCES antibody (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A4A1787654985755 FOREIGN KEY (protein_id) REFERENCES protein (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO antibody_protein (antibody_id, protein_id) SELECT antibody_id, protein_id FROM __temp__antibody_protein');
        $this->addSql('DROP TABLE __temp__antibody_protein');
        $this->addSql('CREATE INDEX IDX_A4A1787654985755 ON antibody_protein (protein_id)');
        $this->addSql('CREATE INDEX IDX_A4A1787651162764 ON antibody_protein (antibody_id)');
        $this->addSql('DROP INDEX IDX_C0A299811C123AD8');
        $this->addSql('DROP INDEX IDX_C0A299815F76A57');
        $this->addSql('CREATE TEMPORARY TABLE __temp__antibody_antibody AS SELECT antibody_source, antibody_target FROM antibody_antibody');
        $this->addSql('DROP TABLE antibody_antibody');
        $this->addSql('CREATE TABLE antibody_antibody (antibody_source INTEGER NOT NULL, antibody_target INTEGER NOT NULL, PRIMARY KEY(antibody_source, antibody_target), CONSTRAINT FK_C0A299815F76A57 FOREIGN KEY (antibody_source) REFERENCES antibody (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_C0A299811C123AD8 FOREIGN KEY (antibody_target) REFERENCES antibody (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO antibody_antibody (antibody_source, antibody_target) SELECT antibody_source, antibody_target FROM __temp__antibody_antibody');
        $this->addSql('DROP TABLE __temp__antibody_antibody');
        $this->addSql('CREATE INDEX IDX_C0A299811C123AD8 ON antibody_antibody (antibody_target)');
        $this->addSql('CREATE INDEX IDX_C0A299815F76A57 ON antibody_antibody (antibody_source)');
        $this->addSql('DROP INDEX IDX_16A87BE3FF444C8');
        $this->addSql('DROP INDEX IDX_16A87BE3EB0F4B39');
        $this->addSql('DROP INDEX IDX_16A87BE351162764');
        $this->addSql('CREATE TEMPORARY TABLE __temp__antibody_dilution AS SELECT id, antibody_id, experiment_type_id, experiment_id, dilution FROM antibody_dilution');
        $this->addSql('DROP TABLE antibody_dilution');
        $this->addSql('CREATE TABLE antibody_dilution (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, antibody_id INTEGER NOT NULL, experiment_type_id INTEGER DEFAULT NULL, experiment_id INTEGER DEFAULT NULL, dilution VARCHAR(15) NOT NULL COLLATE BINARY, CONSTRAINT FK_16A87BE351162764 FOREIGN KEY (antibody_id) REFERENCES antibody (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_16A87BE3EB0F4B39 FOREIGN KEY (experiment_type_id) REFERENCES experiment_type (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_16A87BE3FF444C8 FOREIGN KEY (experiment_id) REFERENCES experiment (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO antibody_dilution (id, antibody_id, experiment_type_id, experiment_id, dilution) SELECT id, antibody_id, experiment_type_id, experiment_id, dilution FROM __temp__antibody_dilution');
        $this->addSql('DROP TABLE __temp__antibody_dilution');
        $this->addSql('CREATE INDEX IDX_16A87BE3FF444C8 ON antibody_dilution (experiment_id)');
        $this->addSql('CREATE INDEX IDX_16A87BE3EB0F4B39 ON antibody_dilution (experiment_type_id)');
        $this->addSql('CREATE INDEX IDX_16A87BE351162764 ON antibody_dilution (antibody_id)');
        $this->addSql('DROP INDEX IDX_8A9483A8E86A33E');
        $this->addSql('CREATE TEMPORARY TABLE __temp__box AS SELECT id, rack_id, name, rows, cols FROM box');
        $this->addSql('DROP TABLE box');
        $this->addSql('CREATE TABLE box (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rack_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL COLLATE BINARY, rows INTEGER NOT NULL, cols INTEGER NOT NULL, CONSTRAINT FK_8A9483A8E86A33E FOREIGN KEY (rack_id) REFERENCES rack (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO box (id, rack_id, name, rows, cols) SELECT id, rack_id, name, rows, cols FROM __temp__box');
        $this->addSql('DROP TABLE __temp__box');
        $this->addSql('CREATE INDEX IDX_8A9483A8E86A33E ON box (rack_id)');
        $this->addSql('DROP INDEX IDX_CB8787E2DEC6D6BA');
        $this->addSql('DROP INDEX IDX_CB8787E2F603EE73');
        $this->addSql('DROP INDEX IDX_CB8787E2701EFC92');
        $this->addSql('DROP INDEX IDX_CB8787E264180A36');
        $this->addSql('DROP INDEX IDX_CB8787E2B38B33AD');
        $this->addSql('DROP INDEX IDX_CB8787E2727ACA70');
        $this->addSql('DROP INDEX UNIQ_CB8787E25E237E06');
        $this->addSql('CREATE TEMPORARY TABLE __temp__cell AS SELECT id, parent_id, morphology_id, organism_id, tissue_id, vendor_id, bought_by_id, name, age, culture_type, is_cancer, is_engineered, origin, vendor_pn, acquired_on, price, origin_comment, medium, freezing, thawing, culture_conditions, splitting, trypsin, lysing, seeding, count_on_confluence, cell_number FROM cell');
        $this->addSql('DROP TABLE cell');
        $this->addSql('CREATE TABLE cell (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, parent_id INTEGER DEFAULT NULL, morphology_id INTEGER DEFAULT NULL, organism_id INTEGER DEFAULT NULL, tissue_id INTEGER DEFAULT NULL, vendor_id INTEGER DEFAULT NULL, bought_by_id BLOB DEFAULT NULL --(DC2Type:ulid)
        , name VARCHAR(255) NOT NULL COLLATE BINARY, age VARCHAR(255) NOT NULL COLLATE BINARY, culture_type VARCHAR(255) NOT NULL COLLATE BINARY, is_cancer BOOLEAN NOT NULL, is_engineered BOOLEAN NOT NULL, origin VARCHAR(255) DEFAULT NULL COLLATE BINARY, vendor_pn VARCHAR(255) DEFAULT NULL COLLATE BINARY, acquired_on DATETIME DEFAULT NULL, price NUMERIC(7, 2) DEFAULT NULL, origin_comment CLOB DEFAULT NULL COLLATE BINARY, medium VARCHAR(255) DEFAULT NULL COLLATE BINARY, freezing CLOB DEFAULT NULL COLLATE BINARY, thawing CLOB DEFAULT NULL COLLATE BINARY, culture_conditions CLOB DEFAULT NULL COLLATE BINARY, splitting CLOB DEFAULT NULL COLLATE BINARY, trypsin VARCHAR(255) DEFAULT NULL COLLATE BINARY, lysing CLOB DEFAULT NULL COLLATE BINARY, seeding CLOB DEFAULT NULL COLLATE BINARY, count_on_confluence INTEGER DEFAULT NULL, cell_number VARCHAR(10) DEFAULT NULL COLLATE BINARY, CONSTRAINT FK_CB8787E2727ACA70 FOREIGN KEY (parent_id) REFERENCES cell (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CB8787E2B38B33AD FOREIGN KEY (morphology_id) REFERENCES morphology (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CB8787E264180A36 FOREIGN KEY (organism_id) REFERENCES organism (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CB8787E2701EFC92 FOREIGN KEY (tissue_id) REFERENCES tissue (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CB8787E2F603EE73 FOREIGN KEY (vendor_id) REFERENCES vendor (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CB8787E2DEC6D6BA FOREIGN KEY (bought_by_id) REFERENCES user_accounts (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO cell (id, parent_id, morphology_id, organism_id, tissue_id, vendor_id, bought_by_id, name, age, culture_type, is_cancer, is_engineered, origin, vendor_pn, acquired_on, price, origin_comment, medium, freezing, thawing, culture_conditions, splitting, trypsin, lysing, seeding, count_on_confluence, cell_number) SELECT id, parent_id, morphology_id, organism_id, tissue_id, vendor_id, bought_by_id, name, age, culture_type, is_cancer, is_engineered, origin, vendor_pn, acquired_on, price, origin_comment, medium, freezing, thawing, culture_conditions, splitting, trypsin, lysing, seeding, count_on_confluence, cell_number FROM __temp__cell');
        $this->addSql('DROP TABLE __temp__cell');
        $this->addSql('CREATE INDEX IDX_CB8787E2DEC6D6BA ON cell (bought_by_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E2F603EE73 ON cell (vendor_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E2701EFC92 ON cell (tissue_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E264180A36 ON cell (organism_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E2B38B33AD ON cell (morphology_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E2727ACA70 ON cell (parent_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CB8787E25E237E06 ON cell (name)');
        $this->addSql('DROP INDEX IDX_E2BD6163CB39D93A');
        $this->addSql('DROP INDEX IDX_E2BD6163D8177B3F');
        $this->addSql('DROP INDEX IDX_E2BD6163C88E0642');
        $this->addSql('CREATE TEMPORARY TABLE __temp__cell_aliquote AS SELECT id, aliquoted_by_id, box_id, cell_id, aliquoted_on, vial_color, vials, passage, cell_count, mycoplasma, typing, history, cryo_medium FROM cell_aliquote');
        $this->addSql('DROP TABLE cell_aliquote');
        $this->addSql('CREATE TABLE cell_aliquote (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, aliquoted_by_id BLOB DEFAULT NULL --(DC2Type:ulid)
        , box_id INTEGER DEFAULT NULL, cell_id INTEGER NOT NULL, aliquoted_on DATETIME NOT NULL, vial_color VARCHAR(30) NOT NULL COLLATE BINARY, vials INTEGER NOT NULL, passage INTEGER NOT NULL, cell_count INTEGER NOT NULL, mycoplasma CLOB DEFAULT NULL COLLATE BINARY, typing CLOB DEFAULT NULL COLLATE BINARY, history CLOB DEFAULT NULL COLLATE BINARY, cryo_medium VARCHAR(255) DEFAULT NULL COLLATE BINARY, CONSTRAINT FK_E2BD6163C88E0642 FOREIGN KEY (aliquoted_by_id) REFERENCES user_accounts (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_E2BD6163D8177B3F FOREIGN KEY (box_id) REFERENCES box (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_E2BD6163CB39D93A FOREIGN KEY (cell_id) REFERENCES cell (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO cell_aliquote (id, aliquoted_by_id, box_id, cell_id, aliquoted_on, vial_color, vials, passage, cell_count, mycoplasma, typing, history, cryo_medium) SELECT id, aliquoted_by_id, box_id, cell_id, aliquoted_on, vial_color, vials, passage, cell_count, mycoplasma, typing, history, cryo_medium FROM __temp__cell_aliquote');
        $this->addSql('DROP TABLE __temp__cell_aliquote');
        $this->addSql('CREATE INDEX IDX_E2BD6163CB39D93A ON cell_aliquote (cell_id)');
        $this->addSql('CREATE INDEX IDX_E2BD6163D8177B3F ON cell_aliquote (box_id)');
        $this->addSql('CREATE INDEX IDX_E2BD6163C88E0642 ON cell_aliquote (aliquoted_by_id)');
        $this->addSql('DROP INDEX IDX_8ED9EDC3F603EE73');
        $this->addSql('CREATE TEMPORARY TABLE __temp__chemical AS SELECT id, vendor_id, long_name, short_name, smiles, labjournal, vendor_pn FROM chemical');
        $this->addSql('DROP TABLE chemical');
        $this->addSql('CREATE TABLE chemical (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, vendor_id INTEGER DEFAULT NULL, long_name VARCHAR(255) NOT NULL COLLATE BINARY, short_name VARCHAR(10) NOT NULL COLLATE BINARY, smiles CLOB NOT NULL COLLATE BINARY, labjournal CLOB DEFAULT NULL COLLATE BINARY, vendor_pn VARCHAR(255) DEFAULT NULL COLLATE BINARY, CONSTRAINT FK_8ED9EDC3F603EE73 FOREIGN KEY (vendor_id) REFERENCES vendor (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO chemical (id, vendor_id, long_name, short_name, smiles, labjournal, vendor_pn) SELECT id, vendor_id, long_name, short_name, smiles, labjournal, vendor_pn FROM __temp__chemical');
        $this->addSql('DROP TABLE __temp__chemical');
        $this->addSql('CREATE INDEX IDX_8ED9EDC3F603EE73 ON chemical (vendor_id)');
        $this->addSql('DROP INDEX IDX_253A1758F603EE73');
        $this->addSql('CREATE TEMPORARY TABLE __temp__culture_flask AS SELECT id, vendor_id, name, rows, cols, comment FROM culture_flask');
        $this->addSql('DROP TABLE culture_flask');
        $this->addSql('CREATE TABLE culture_flask (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, vendor_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL COLLATE BINARY, rows SMALLINT DEFAULT 1 NOT NULL, cols SMALLINT NOT NULL, comment CLOB DEFAULT NULL COLLATE BINARY, CONSTRAINT FK_253A1758F603EE73 FOREIGN KEY (vendor_id) REFERENCES vendor (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO culture_flask (id, vendor_id, name, rows, cols, comment) SELECT id, vendor_id, name, rows, cols, comment FROM __temp__culture_flask');
        $this->addSql('DROP TABLE __temp__culture_flask');
        $this->addSql('CREATE INDEX IDX_253A1758F603EE73 ON culture_flask (vendor_id)');
        $this->addSql('DROP INDEX IDX_136F58B2C14C34C2');
        $this->addSql('DROP INDEX IDX_136F58B2EB0F4B39');
        $this->addSql('DROP INDEX IDX_136F58B27E3C61F9');
        $this->addSql('CREATE TEMPORARY TABLE __temp__experiment AS SELECT id, owner_id, experiment_type_id, wellplate_id, name, lysing, seeding, created_at, modified_at FROM experiment');
        $this->addSql('DROP TABLE experiment');
        $this->addSql('CREATE TABLE experiment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, owner_id BLOB NOT NULL --(DC2Type:ulid)
        , experiment_type_id INTEGER NOT NULL, wellplate_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL COLLATE BINARY, lysing CLOB DEFAULT NULL COLLATE BINARY, seeding CLOB DEFAULT NULL COLLATE BINARY, created_at DATETIME DEFAULT NULL, modified_at DATETIME DEFAULT NULL, CONSTRAINT FK_136F58B27E3C61F9 FOREIGN KEY (owner_id) REFERENCES user_accounts (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_136F58B2EB0F4B39 FOREIGN KEY (experiment_type_id) REFERENCES experiment_type (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_136F58B2C14C34C2 FOREIGN KEY (wellplate_id) REFERENCES culture_flask (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO experiment (id, owner_id, experiment_type_id, wellplate_id, name, lysing, seeding, created_at, modified_at) SELECT id, owner_id, experiment_type_id, wellplate_id, name, lysing, seeding, created_at, modified_at FROM __temp__experiment');
        $this->addSql('DROP TABLE __temp__experiment');
        $this->addSql('CREATE INDEX IDX_136F58B2C14C34C2 ON experiment (wellplate_id)');
        $this->addSql('CREATE INDEX IDX_136F58B2EB0F4B39 ON experiment (experiment_type_id)');
        $this->addSql('CREATE INDEX IDX_136F58B27E3C61F9 ON experiment (owner_id)');
        $this->addSql('DROP INDEX IDX_B6BB261854985755');
        $this->addSql('DROP INDEX IDX_B6BB2618FF444C8');
        $this->addSql('CREATE TEMPORARY TABLE __temp__experiment_protein AS SELECT experiment_id, protein_id FROM experiment_protein');
        $this->addSql('DROP TABLE experiment_protein');
        $this->addSql('CREATE TABLE experiment_protein (experiment_id INTEGER NOT NULL, protein_id INTEGER NOT NULL, PRIMARY KEY(experiment_id, protein_id), CONSTRAINT FK_B6BB2618FF444C8 FOREIGN KEY (experiment_id) REFERENCES experiment (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B6BB261854985755 FOREIGN KEY (protein_id) REFERENCES protein (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO experiment_protein (experiment_id, protein_id) SELECT experiment_id, protein_id FROM __temp__experiment_protein');
        $this->addSql('DROP TABLE __temp__experiment_protein');
        $this->addSql('CREATE INDEX IDX_B6BB261854985755 ON experiment_protein (protein_id)');
        $this->addSql('CREATE INDEX IDX_B6BB2618FF444C8 ON experiment_protein (experiment_id)');
        $this->addSql('DROP INDEX IDX_B8F4E4F2E1770A76');
        $this->addSql('DROP INDEX IDX_B8F4E4F2FF444C8');
        $this->addSql('CREATE TEMPORARY TABLE __temp__experiment_chemical AS SELECT experiment_id, chemical_id FROM experiment_chemical');
        $this->addSql('DROP TABLE experiment_chemical');
        $this->addSql('CREATE TABLE experiment_chemical (experiment_id INTEGER NOT NULL, chemical_id INTEGER NOT NULL, PRIMARY KEY(experiment_id, chemical_id), CONSTRAINT FK_B8F4E4F2FF444C8 FOREIGN KEY (experiment_id) REFERENCES experiment (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B8F4E4F2E1770A76 FOREIGN KEY (chemical_id) REFERENCES chemical (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO experiment_chemical (experiment_id, chemical_id) SELECT experiment_id, chemical_id FROM __temp__experiment_chemical');
        $this->addSql('DROP TABLE __temp__experiment_chemical');
        $this->addSql('CREATE INDEX IDX_B8F4E4F2E1770A76 ON experiment_chemical (chemical_id)');
        $this->addSql('CREATE INDEX IDX_B8F4E4F2FF444C8 ON experiment_chemical (experiment_id)');
        $this->addSql('DROP INDEX IDX_D078464FCB39D93A');
        $this->addSql('DROP INDEX IDX_D078464FFF444C8');
        $this->addSql('CREATE TEMPORARY TABLE __temp__experiment_cell AS SELECT experiment_id, cell_id FROM experiment_cell');
        $this->addSql('DROP TABLE experiment_cell');
        $this->addSql('CREATE TABLE experiment_cell (experiment_id INTEGER NOT NULL, cell_id INTEGER NOT NULL, PRIMARY KEY(experiment_id, cell_id), CONSTRAINT FK_D078464FFF444C8 FOREIGN KEY (experiment_id) REFERENCES experiment (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D078464FCB39D93A FOREIGN KEY (cell_id) REFERENCES cell (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO experiment_cell (experiment_id, cell_id) SELECT experiment_id, cell_id FROM __temp__experiment_cell');
        $this->addSql('DROP TABLE __temp__experiment_cell');
        $this->addSql('CREATE INDEX IDX_D078464FCB39D93A ON experiment_cell (cell_id)');
        $this->addSql('CREATE INDEX IDX_D078464FFF444C8 ON experiment_cell (experiment_id)');
        $this->addSql('DROP INDEX IDX_97219684C14C34C2');
        $this->addSql('DROP INDEX IDX_97219684727ACA70');
        $this->addSql('CREATE TEMPORARY TABLE __temp__experiment_type AS SELECT id, parent_id, wellplate_id, name, description, lysing, seeding FROM experiment_type');
        $this->addSql('DROP TABLE experiment_type');
        $this->addSql('CREATE TABLE experiment_type (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, parent_id INTEGER DEFAULT NULL, wellplate_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL COLLATE BINARY, description CLOB DEFAULT NULL COLLATE BINARY, lysing CLOB DEFAULT NULL COLLATE BINARY, seeding CLOB DEFAULT NULL COLLATE BINARY, CONSTRAINT FK_97219684727ACA70 FOREIGN KEY (parent_id) REFERENCES experiment_type (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_97219684C14C34C2 FOREIGN KEY (wellplate_id) REFERENCES culture_flask (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO experiment_type (id, parent_id, wellplate_id, name, description, lysing, seeding) SELECT id, parent_id, wellplate_id, name, description, lysing, seeding FROM __temp__experiment_type');
        $this->addSql('DROP TABLE __temp__experiment_type');
        $this->addSql('CREATE INDEX IDX_97219684C14C34C2 ON experiment_type (wellplate_id)');
        $this->addSql('CREATE INDEX IDX_97219684727ACA70 ON experiment_type (parent_id)');
        $this->addSql('DROP INDEX IDX_8C9F3610A2B28FE8');
        $this->addSql('CREATE TEMPORARY TABLE __temp__file AS SELECT id, uploaded_by_id, content_type, content, content_size FROM file');
        $this->addSql('DROP TABLE file');
        $this->addSql('CREATE TABLE file (id BLOB NOT NULL --(DC2Type:uuid)
        , uploaded_by_id BLOB NOT NULL --(DC2Type:ulid)
        , content_type VARCHAR(255) NOT NULL COLLATE BINARY, content BLOB NOT NULL, content_size INTEGER NOT NULL, CONSTRAINT FK_8C9F3610A2B28FE8 FOREIGN KEY (uploaded_by_id) REFERENCES user_accounts (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO file (id, uploaded_by_id, content_type, content, content_size) SELECT id, uploaded_by_id, content_type, content, content_size FROM __temp__file');
        $this->addSql('DROP TABLE __temp__file');
        $this->addSql('CREATE INDEX IDX_8C9F3610A2B28FE8 ON file (uploaded_by_id)');
        $this->addSql('DROP INDEX IDX_B6BD307FF675F31B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__message AS SELECT id, author_id, title, body, date FROM message');
        $this->addSql('DROP TABLE message');
        $this->addSql('CREATE TABLE message (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, author_id BLOB NOT NULL --(DC2Type:ulid)
        , title VARCHAR(255) NOT NULL COLLATE BINARY, body CLOB NOT NULL COLLATE BINARY, date DATETIME DEFAULT NULL, CONSTRAINT FK_B6BD307FF675F31B FOREIGN KEY (author_id) REFERENCES user_accounts (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO message (id, author_id, title, body, date) SELECT id, author_id, title, body, date FROM __temp__message');
        $this->addSql('DROP TABLE __temp__message');
        $this->addSql('CREATE INDEX IDX_B6BD307FF675F31B ON message (author_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_5C97C6B1F603EE73');
        $this->addSql('CREATE TEMPORARY TABLE __temp__antibody AS SELECT id, vendor_id, short_name, long_name, vendor_pn, detection, number FROM antibody');
        $this->addSql('DROP TABLE antibody');
        $this->addSql('CREATE TABLE antibody (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, vendor_id INTEGER DEFAULT NULL, short_name VARCHAR(255) NOT NULL, long_name VARCHAR(255) NOT NULL, vendor_pn VARCHAR(50) DEFAULT NULL, detection VARCHAR(255) DEFAULT NULL, number VARCHAR(10) DEFAULT NULL)');
        $this->addSql('INSERT INTO antibody (id, vendor_id, short_name, long_name, vendor_pn, detection, number) SELECT id, vendor_id, short_name, long_name, vendor_pn, detection, number FROM __temp__antibody');
        $this->addSql('DROP TABLE __temp__antibody');
        $this->addSql('CREATE INDEX IDX_5C97C6B1F603EE73 ON antibody (vendor_id)');
        $this->addSql('DROP INDEX IDX_C0A299815F76A57');
        $this->addSql('DROP INDEX IDX_C0A299811C123AD8');
        $this->addSql('CREATE TEMPORARY TABLE __temp__antibody_antibody AS SELECT antibody_source, antibody_target FROM antibody_antibody');
        $this->addSql('DROP TABLE antibody_antibody');
        $this->addSql('CREATE TABLE antibody_antibody (antibody_source INTEGER NOT NULL, antibody_target INTEGER NOT NULL, PRIMARY KEY(antibody_source, antibody_target))');
        $this->addSql('INSERT INTO antibody_antibody (antibody_source, antibody_target) SELECT antibody_source, antibody_target FROM __temp__antibody_antibody');
        $this->addSql('DROP TABLE __temp__antibody_antibody');
        $this->addSql('CREATE INDEX IDX_C0A299815F76A57 ON antibody_antibody (antibody_source)');
        $this->addSql('CREATE INDEX IDX_C0A299811C123AD8 ON antibody_antibody (antibody_target)');
        $this->addSql('DROP INDEX IDX_16A87BE351162764');
        $this->addSql('DROP INDEX IDX_16A87BE3EB0F4B39');
        $this->addSql('DROP INDEX IDX_16A87BE3FF444C8');
        $this->addSql('CREATE TEMPORARY TABLE __temp__antibody_dilution AS SELECT id, antibody_id, experiment_type_id, experiment_id, dilution FROM antibody_dilution');
        $this->addSql('DROP TABLE antibody_dilution');
        $this->addSql('CREATE TABLE antibody_dilution (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, antibody_id INTEGER NOT NULL, experiment_type_id INTEGER DEFAULT NULL, experiment_id INTEGER DEFAULT NULL, dilution VARCHAR(15) NOT NULL)');
        $this->addSql('INSERT INTO antibody_dilution (id, antibody_id, experiment_type_id, experiment_id, dilution) SELECT id, antibody_id, experiment_type_id, experiment_id, dilution FROM __temp__antibody_dilution');
        $this->addSql('DROP TABLE __temp__antibody_dilution');
        $this->addSql('CREATE INDEX IDX_16A87BE351162764 ON antibody_dilution (antibody_id)');
        $this->addSql('CREATE INDEX IDX_16A87BE3EB0F4B39 ON antibody_dilution (experiment_type_id)');
        $this->addSql('CREATE INDEX IDX_16A87BE3FF444C8 ON antibody_dilution (experiment_id)');
        $this->addSql('DROP INDEX IDX_A4A1787651162764');
        $this->addSql('DROP INDEX IDX_A4A1787654985755');
        $this->addSql('CREATE TEMPORARY TABLE __temp__antibody_protein AS SELECT antibody_id, protein_id FROM antibody_protein');
        $this->addSql('DROP TABLE antibody_protein');
        $this->addSql('CREATE TABLE antibody_protein (antibody_id INTEGER NOT NULL, protein_id INTEGER NOT NULL, PRIMARY KEY(antibody_id, protein_id))');
        $this->addSql('INSERT INTO antibody_protein (antibody_id, protein_id) SELECT antibody_id, protein_id FROM __temp__antibody_protein');
        $this->addSql('DROP TABLE __temp__antibody_protein');
        $this->addSql('CREATE INDEX IDX_A4A1787651162764 ON antibody_protein (antibody_id)');
        $this->addSql('CREATE INDEX IDX_A4A1787654985755 ON antibody_protein (protein_id)');
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
        $this->addSql('DROP INDEX IDX_CB8787E2F603EE73');
        $this->addSql('DROP INDEX IDX_CB8787E2DEC6D6BA');
        $this->addSql('CREATE TEMPORARY TABLE __temp__cell AS SELECT id, parent_id, morphology_id, organism_id, tissue_id, vendor_id, bought_by_id, name, age, culture_type, is_cancer, is_engineered, origin, vendor_pn, acquired_on, price, origin_comment, medium, freezing, thawing, culture_conditions, splitting, trypsin, lysing, seeding, count_on_confluence, cell_number FROM cell');
        $this->addSql('DROP TABLE cell');
        $this->addSql('CREATE TABLE cell (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, parent_id INTEGER DEFAULT NULL, morphology_id INTEGER DEFAULT NULL, organism_id INTEGER DEFAULT NULL, tissue_id INTEGER DEFAULT NULL, vendor_id INTEGER DEFAULT NULL, bought_by_id BLOB DEFAULT NULL --(DC2Type:ulid)
        , name VARCHAR(255) NOT NULL, age VARCHAR(255) NOT NULL, culture_type VARCHAR(255) NOT NULL, is_cancer BOOLEAN NOT NULL, is_engineered BOOLEAN NOT NULL, origin VARCHAR(255) DEFAULT NULL, vendor_pn VARCHAR(255) DEFAULT NULL, acquired_on DATETIME DEFAULT NULL, price NUMERIC(7, 2) DEFAULT NULL, origin_comment CLOB DEFAULT NULL, medium VARCHAR(255) DEFAULT NULL, freezing CLOB DEFAULT NULL, thawing CLOB DEFAULT NULL, culture_conditions CLOB DEFAULT NULL, splitting CLOB DEFAULT NULL, trypsin VARCHAR(255) DEFAULT NULL, lysing CLOB DEFAULT NULL, seeding CLOB DEFAULT NULL, count_on_confluence INTEGER DEFAULT NULL, cell_number VARCHAR(10) DEFAULT NULL)');
        $this->addSql('INSERT INTO cell (id, parent_id, morphology_id, organism_id, tissue_id, vendor_id, bought_by_id, name, age, culture_type, is_cancer, is_engineered, origin, vendor_pn, acquired_on, price, origin_comment, medium, freezing, thawing, culture_conditions, splitting, trypsin, lysing, seeding, count_on_confluence, cell_number) SELECT id, parent_id, morphology_id, organism_id, tissue_id, vendor_id, bought_by_id, name, age, culture_type, is_cancer, is_engineered, origin, vendor_pn, acquired_on, price, origin_comment, medium, freezing, thawing, culture_conditions, splitting, trypsin, lysing, seeding, count_on_confluence, cell_number FROM __temp__cell');
        $this->addSql('DROP TABLE __temp__cell');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CB8787E25E237E06 ON cell (name)');
        $this->addSql('CREATE INDEX IDX_CB8787E2727ACA70 ON cell (parent_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E2B38B33AD ON cell (morphology_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E264180A36 ON cell (organism_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E2701EFC92 ON cell (tissue_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E2F603EE73 ON cell (vendor_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E2DEC6D6BA ON cell (bought_by_id)');
        $this->addSql('DROP INDEX IDX_E2BD6163C88E0642');
        $this->addSql('DROP INDEX IDX_E2BD6163D8177B3F');
        $this->addSql('DROP INDEX IDX_E2BD6163CB39D93A');
        $this->addSql('CREATE TEMPORARY TABLE __temp__cell_aliquote AS SELECT id, aliquoted_by_id, box_id, cell_id, aliquoted_on, vial_color, vials, passage, cell_count, mycoplasma, typing, history, cryo_medium FROM cell_aliquote');
        $this->addSql('DROP TABLE cell_aliquote');
        $this->addSql('CREATE TABLE cell_aliquote (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, aliquoted_by_id BLOB DEFAULT NULL --(DC2Type:ulid)
        , box_id INTEGER DEFAULT NULL, cell_id INTEGER NOT NULL, aliquoted_on DATETIME NOT NULL, vial_color VARCHAR(30) NOT NULL, vials INTEGER NOT NULL, passage INTEGER NOT NULL, cell_count INTEGER NOT NULL, mycoplasma CLOB DEFAULT NULL, typing CLOB DEFAULT NULL, history CLOB DEFAULT NULL, cryo_medium VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO cell_aliquote (id, aliquoted_by_id, box_id, cell_id, aliquoted_on, vial_color, vials, passage, cell_count, mycoplasma, typing, history, cryo_medium) SELECT id, aliquoted_by_id, box_id, cell_id, aliquoted_on, vial_color, vials, passage, cell_count, mycoplasma, typing, history, cryo_medium FROM __temp__cell_aliquote');
        $this->addSql('DROP TABLE __temp__cell_aliquote');
        $this->addSql('CREATE INDEX IDX_E2BD6163C88E0642 ON cell_aliquote (aliquoted_by_id)');
        $this->addSql('CREATE INDEX IDX_E2BD6163D8177B3F ON cell_aliquote (box_id)');
        $this->addSql('CREATE INDEX IDX_E2BD6163CB39D93A ON cell_aliquote (cell_id)');
        $this->addSql('DROP INDEX IDX_8ED9EDC3F603EE73');
        $this->addSql('CREATE TEMPORARY TABLE __temp__chemical AS SELECT id, vendor_id, long_name, short_name, smiles, labjournal, vendor_pn FROM chemical');
        $this->addSql('DROP TABLE chemical');
        $this->addSql('CREATE TABLE chemical (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, vendor_id INTEGER DEFAULT NULL, long_name VARCHAR(255) NOT NULL, short_name VARCHAR(10) NOT NULL, smiles CLOB NOT NULL, labjournal CLOB DEFAULT NULL, vendor_pn VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO chemical (id, vendor_id, long_name, short_name, smiles, labjournal, vendor_pn) SELECT id, vendor_id, long_name, short_name, smiles, labjournal, vendor_pn FROM __temp__chemical');
        $this->addSql('DROP TABLE __temp__chemical');
        $this->addSql('CREATE INDEX IDX_8ED9EDC3F603EE73 ON chemical (vendor_id)');
        $this->addSql('DROP INDEX IDX_253A1758F603EE73');
        $this->addSql('CREATE TEMPORARY TABLE __temp__culture_flask AS SELECT id, vendor_id, name, rows, cols, comment FROM culture_flask');
        $this->addSql('DROP TABLE culture_flask');
        $this->addSql('CREATE TABLE culture_flask (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, vendor_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, rows SMALLINT DEFAULT 1 NOT NULL, cols SMALLINT NOT NULL, comment CLOB DEFAULT NULL)');
        $this->addSql('INSERT INTO culture_flask (id, vendor_id, name, rows, cols, comment) SELECT id, vendor_id, name, rows, cols, comment FROM __temp__culture_flask');
        $this->addSql('DROP TABLE __temp__culture_flask');
        $this->addSql('CREATE INDEX IDX_253A1758F603EE73 ON culture_flask (vendor_id)');
        $this->addSql('DROP INDEX IDX_136F58B27E3C61F9');
        $this->addSql('DROP INDEX IDX_136F58B2EB0F4B39');
        $this->addSql('DROP INDEX IDX_136F58B2C14C34C2');
        $this->addSql('CREATE TEMPORARY TABLE __temp__experiment AS SELECT id, owner_id, experiment_type_id, wellplate_id, name, lysing, seeding, created_at, modified_at FROM experiment');
        $this->addSql('DROP TABLE experiment');
        $this->addSql('CREATE TABLE experiment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, owner_id BLOB NOT NULL --(DC2Type:ulid)
        , experiment_type_id INTEGER NOT NULL, wellplate_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, lysing CLOB DEFAULT NULL, seeding CLOB DEFAULT NULL, created_at DATETIME DEFAULT NULL, modified_at DATETIME DEFAULT NULL)');
        $this->addSql('INSERT INTO experiment (id, owner_id, experiment_type_id, wellplate_id, name, lysing, seeding, created_at, modified_at) SELECT id, owner_id, experiment_type_id, wellplate_id, name, lysing, seeding, created_at, modified_at FROM __temp__experiment');
        $this->addSql('DROP TABLE __temp__experiment');
        $this->addSql('CREATE INDEX IDX_136F58B27E3C61F9 ON experiment (owner_id)');
        $this->addSql('CREATE INDEX IDX_136F58B2EB0F4B39 ON experiment (experiment_type_id)');
        $this->addSql('CREATE INDEX IDX_136F58B2C14C34C2 ON experiment (wellplate_id)');
        $this->addSql('DROP INDEX IDX_D078464FFF444C8');
        $this->addSql('DROP INDEX IDX_D078464FCB39D93A');
        $this->addSql('CREATE TEMPORARY TABLE __temp__experiment_cell AS SELECT experiment_id, cell_id FROM experiment_cell');
        $this->addSql('DROP TABLE experiment_cell');
        $this->addSql('CREATE TABLE experiment_cell (experiment_id INTEGER NOT NULL, cell_id INTEGER NOT NULL, PRIMARY KEY(experiment_id, cell_id))');
        $this->addSql('INSERT INTO experiment_cell (experiment_id, cell_id) SELECT experiment_id, cell_id FROM __temp__experiment_cell');
        $this->addSql('DROP TABLE __temp__experiment_cell');
        $this->addSql('CREATE INDEX IDX_D078464FFF444C8 ON experiment_cell (experiment_id)');
        $this->addSql('CREATE INDEX IDX_D078464FCB39D93A ON experiment_cell (cell_id)');
        $this->addSql('DROP INDEX IDX_B8F4E4F2FF444C8');
        $this->addSql('DROP INDEX IDX_B8F4E4F2E1770A76');
        $this->addSql('CREATE TEMPORARY TABLE __temp__experiment_chemical AS SELECT experiment_id, chemical_id FROM experiment_chemical');
        $this->addSql('DROP TABLE experiment_chemical');
        $this->addSql('CREATE TABLE experiment_chemical (experiment_id INTEGER NOT NULL, chemical_id INTEGER NOT NULL, PRIMARY KEY(experiment_id, chemical_id))');
        $this->addSql('INSERT INTO experiment_chemical (experiment_id, chemical_id) SELECT experiment_id, chemical_id FROM __temp__experiment_chemical');
        $this->addSql('DROP TABLE __temp__experiment_chemical');
        $this->addSql('CREATE INDEX IDX_B8F4E4F2FF444C8 ON experiment_chemical (experiment_id)');
        $this->addSql('CREATE INDEX IDX_B8F4E4F2E1770A76 ON experiment_chemical (chemical_id)');
        $this->addSql('DROP INDEX IDX_B6BB2618FF444C8');
        $this->addSql('DROP INDEX IDX_B6BB261854985755');
        $this->addSql('CREATE TEMPORARY TABLE __temp__experiment_protein AS SELECT experiment_id, protein_id FROM experiment_protein');
        $this->addSql('DROP TABLE experiment_protein');
        $this->addSql('CREATE TABLE experiment_protein (experiment_id INTEGER NOT NULL, protein_id INTEGER NOT NULL, PRIMARY KEY(experiment_id, protein_id))');
        $this->addSql('INSERT INTO experiment_protein (experiment_id, protein_id) SELECT experiment_id, protein_id FROM __temp__experiment_protein');
        $this->addSql('DROP TABLE __temp__experiment_protein');
        $this->addSql('CREATE INDEX IDX_B6BB2618FF444C8 ON experiment_protein (experiment_id)');
        $this->addSql('CREATE INDEX IDX_B6BB261854985755 ON experiment_protein (protein_id)');
        $this->addSql('DROP INDEX IDX_97219684727ACA70');
        $this->addSql('DROP INDEX IDX_97219684C14C34C2');
        $this->addSql('CREATE TEMPORARY TABLE __temp__experiment_type AS SELECT id, parent_id, wellplate_id, name, description, lysing, seeding FROM experiment_type');
        $this->addSql('DROP TABLE experiment_type');
        $this->addSql('CREATE TABLE experiment_type (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, parent_id INTEGER DEFAULT NULL, wellplate_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, lysing CLOB DEFAULT NULL, seeding CLOB DEFAULT NULL)');
        $this->addSql('INSERT INTO experiment_type (id, parent_id, wellplate_id, name, description, lysing, seeding) SELECT id, parent_id, wellplate_id, name, description, lysing, seeding FROM __temp__experiment_type');
        $this->addSql('DROP TABLE __temp__experiment_type');
        $this->addSql('CREATE INDEX IDX_97219684727ACA70 ON experiment_type (parent_id)');
        $this->addSql('CREATE INDEX IDX_97219684C14C34C2 ON experiment_type (wellplate_id)');
        $this->addSql('DROP INDEX IDX_8C9F3610A2B28FE8');
        $this->addSql('CREATE TEMPORARY TABLE __temp__file AS SELECT id, uploaded_by_id, content_type, content, content_size FROM file');
        $this->addSql('DROP TABLE file');
        $this->addSql('CREATE TABLE file (uploaded_by_id BLOB NOT NULL --(DC2Type:ulid)
        , content_type VARCHAR(255) NOT NULL, content BLOB NOT NULL, content_size INTEGER NOT NULL, id BLOB NOT NULL --(DC2Type:uuid)
        )');
        $this->addSql('INSERT INTO file (id, uploaded_by_id, content_type, content, content_size) SELECT id, uploaded_by_id, content_type, content, content_size FROM __temp__file');
        $this->addSql('DROP TABLE __temp__file');
        $this->addSql('CREATE INDEX IDX_8C9F3610A2B28FE8 ON file (uploaded_by_id)');
        $this->addSql('DROP INDEX IDX_B6BD307FF675F31B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__message AS SELECT id, author_id, title, body, date FROM message');
        $this->addSql('DROP TABLE message');
        $this->addSql('CREATE TABLE message (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, author_id BLOB NOT NULL --(DC2Type:ulid)
        , title VARCHAR(255) NOT NULL, body CLOB NOT NULL, date DATETIME DEFAULT NULL)');
        $this->addSql('INSERT INTO message (id, author_id, title, body, date) SELECT id, author_id, title, body, date FROM __temp__message');
        $this->addSql('DROP TABLE __temp__message');
        $this->addSql('CREATE INDEX IDX_B6BD307FF675F31B ON message (author_id)');
    }
}
