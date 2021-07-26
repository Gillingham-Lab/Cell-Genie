<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210726130818 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE antibody (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, vendor_id INTEGER DEFAULT NULL, short_name VARCHAR(255) NOT NULL, long_name VARCHAR(255) NOT NULL, vendor_pn VARCHAR(50) DEFAULT NULL, detection VARCHAR(255) DEFAULT NULL, number VARCHAR(10) DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_5C97C6B1F603EE73 ON antibody (vendor_id)');
        $this->addSql('CREATE TABLE antibody_protein (antibody_id INTEGER NOT NULL, protein_id INTEGER NOT NULL, PRIMARY KEY(antibody_id, protein_id))');
        $this->addSql('CREATE INDEX IDX_A4A1787651162764 ON antibody_protein (antibody_id)');
        $this->addSql('CREATE INDEX IDX_A4A1787654985755 ON antibody_protein (protein_id)');
        $this->addSql('CREATE TABLE antibody_antibody (antibody_source INTEGER NOT NULL, antibody_target INTEGER NOT NULL, PRIMARY KEY(antibody_source, antibody_target))');
        $this->addSql('CREATE INDEX IDX_C0A299815F76A57 ON antibody_antibody (antibody_source)');
        $this->addSql('CREATE INDEX IDX_C0A299811C123AD8 ON antibody_antibody (antibody_target)');
        $this->addSql('CREATE TABLE antibody_dilution (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, antibody_id INTEGER NOT NULL, experiment_type_id INTEGER DEFAULT NULL, experiment_id INTEGER DEFAULT NULL, dilution VARCHAR(15) NOT NULL)');
        $this->addSql('CREATE INDEX IDX_16A87BE351162764 ON antibody_dilution (antibody_id)');
        $this->addSql('CREATE INDEX IDX_16A87BE3EB0F4B39 ON antibody_dilution (experiment_type_id)');
        $this->addSql('CREATE INDEX IDX_16A87BE3FF444C8 ON antibody_dilution (experiment_id)');
        $this->addSql('CREATE TABLE box (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rack_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, rows INTEGER NOT NULL, cols INTEGER NOT NULL)');
        $this->addSql('CREATE INDEX IDX_8A9483A8E86A33E ON box (rack_id)');
        $this->addSql('CREATE TABLE box_entry (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, box_row INTEGER NOT NULL, box_col INTEGER NOT NULL)');
        $this->addSql('CREATE TABLE cell (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, parent_id INTEGER DEFAULT NULL, morphology_id INTEGER DEFAULT NULL, organism_id INTEGER DEFAULT NULL, tissue_id INTEGER DEFAULT NULL, vendor_id INTEGER DEFAULT NULL, bought_by_id BLOB DEFAULT NULL --(DC2Type:ulid)
        , name VARCHAR(255) NOT NULL, age VARCHAR(255) NOT NULL, culture_type VARCHAR(255) NOT NULL, is_cancer BOOLEAN NOT NULL, is_engineered BOOLEAN NOT NULL, origin VARCHAR(255) DEFAULT NULL, vendor_pn VARCHAR(255) DEFAULT NULL, acquired_on DATETIME DEFAULT NULL, price NUMERIC(7, 2) DEFAULT NULL, origin_comment CLOB DEFAULT NULL, medium VARCHAR(255) DEFAULT NULL, freezing CLOB DEFAULT NULL, thawing CLOB DEFAULT NULL, culture_conditions CLOB DEFAULT NULL, splitting CLOB DEFAULT NULL, trypsin VARCHAR(255) DEFAULT NULL, lysing CLOB DEFAULT NULL, seeding CLOB DEFAULT NULL, count_on_confluence INTEGER DEFAULT NULL, cell_number VARCHAR(10) DEFAULT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CB8787E25E237E06 ON cell (name)');
        $this->addSql('CREATE INDEX IDX_CB8787E2727ACA70 ON cell (parent_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E2B38B33AD ON cell (morphology_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E264180A36 ON cell (organism_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E2701EFC92 ON cell (tissue_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E2F603EE73 ON cell (vendor_id)');
        $this->addSql('CREATE INDEX IDX_CB8787E2DEC6D6BA ON cell (bought_by_id)');
        $this->addSql('CREATE TABLE cell_aliquote (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, aliquoted_by_id BLOB DEFAULT NULL --(DC2Type:ulid)
        , box_id INTEGER DEFAULT NULL, cell_id INTEGER NOT NULL, aliquoted_on DATETIME NOT NULL, vial_color VARCHAR(30) NOT NULL, vials INTEGER NOT NULL, passage INTEGER NOT NULL, cell_count INTEGER NOT NULL, mycoplasma CLOB DEFAULT NULL, typing CLOB DEFAULT NULL, history CLOB DEFAULT NULL, cryo_medium VARCHAR(255) DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_E2BD6163C88E0642 ON cell_aliquote (aliquoted_by_id)');
        $this->addSql('CREATE INDEX IDX_E2BD6163D8177B3F ON cell_aliquote (box_id)');
        $this->addSql('CREATE INDEX IDX_E2BD6163CB39D93A ON cell_aliquote (cell_id)');
        $this->addSql('CREATE TABLE chemical (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, vendor_id INTEGER DEFAULT NULL, long_name VARCHAR(255) NOT NULL, short_name VARCHAR(10) NOT NULL, smiles CLOB NOT NULL, labjournal CLOB DEFAULT NULL, vendor_pn VARCHAR(255) DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_8ED9EDC3F603EE73 ON chemical (vendor_id)');
        $this->addSql('CREATE TABLE culture_flask (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, vendor_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, rows SMALLINT DEFAULT 1 NOT NULL, cols SMALLINT NOT NULL, comment CLOB DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_253A1758F603EE73 ON culture_flask (vendor_id)');
        $this->addSql('CREATE TABLE experiment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, owner_id BLOB NOT NULL --(DC2Type:ulid)
        , experiment_type_id INTEGER NOT NULL, wellplate_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, lysing CLOB DEFAULT NULL, seeding CLOB DEFAULT NULL, created_at DATETIME DEFAULT NULL, modified_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_136F58B27E3C61F9 ON experiment (owner_id)');
        $this->addSql('CREATE INDEX IDX_136F58B2EB0F4B39 ON experiment (experiment_type_id)');
        $this->addSql('CREATE INDEX IDX_136F58B2C14C34C2 ON experiment (wellplate_id)');
        $this->addSql('CREATE TABLE experiment_protein (experiment_id INTEGER NOT NULL, protein_id INTEGER NOT NULL, PRIMARY KEY(experiment_id, protein_id))');
        $this->addSql('CREATE INDEX IDX_B6BB2618FF444C8 ON experiment_protein (experiment_id)');
        $this->addSql('CREATE INDEX IDX_B6BB261854985755 ON experiment_protein (protein_id)');
        $this->addSql('CREATE TABLE experiment_chemical (experiment_id INTEGER NOT NULL, chemical_id INTEGER NOT NULL, PRIMARY KEY(experiment_id, chemical_id))');
        $this->addSql('CREATE INDEX IDX_B8F4E4F2FF444C8 ON experiment_chemical (experiment_id)');
        $this->addSql('CREATE INDEX IDX_B8F4E4F2E1770A76 ON experiment_chemical (chemical_id)');
        $this->addSql('CREATE TABLE experiment_cell (experiment_id INTEGER NOT NULL, cell_id INTEGER NOT NULL, PRIMARY KEY(experiment_id, cell_id))');
        $this->addSql('CREATE INDEX IDX_D078464FFF444C8 ON experiment_cell (experiment_id)');
        $this->addSql('CREATE INDEX IDX_D078464FCB39D93A ON experiment_cell (cell_id)');
        $this->addSql('CREATE TABLE experiment_type (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, parent_id INTEGER DEFAULT NULL, wellplate_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, lysing CLOB DEFAULT NULL, seeding CLOB DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_97219684727ACA70 ON experiment_type (parent_id)');
        $this->addSql('CREATE INDEX IDX_97219684C14C34C2 ON experiment_type (wellplate_id)');
        $this->addSql('CREATE TABLE file (id BLOB NOT NULL --(DC2Type:uuid)
        , uploaded_by_id BLOB NOT NULL --(DC2Type:ulid)
        , content_type VARCHAR(255) NOT NULL, content BLOB NOT NULL, content_size INTEGER NOT NULL)');
        $this->addSql('CREATE INDEX IDX_8C9F3610A2B28FE8 ON file (uploaded_by_id)');
        $this->addSql('CREATE TABLE message (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, author_id BLOB NOT NULL --(DC2Type:ulid)
        , title VARCHAR(255) NOT NULL, body CLOB NOT NULL, date DATETIME DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_B6BD307FF675F31B ON message (author_id)');
        $this->addSql('CREATE TABLE morphology (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE organism (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE protein (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, short_name VARCHAR(10) NOT NULL, long_name VARCHAR(255) NOT NULL, protein_atlas_uri VARCHAR(255) DEFAULT NULL)');
        $this->addSql('CREATE TABLE rack (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, max_boxes INTEGER NOT NULL)');
        $this->addSql('CREATE TABLE tissue (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE user_accounts (id BLOB NOT NULL --(DC2Type:ulid)
        , full_name VARCHAR(255) NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, is_admin BOOLEAN DEFAULT NULL, is_active BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2A457AACDBC463C4 ON user_accounts (full_name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2A457AACE7927C74 ON user_accounts (email)');
        $this->addSql('CREATE TABLE vendor (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, catalog_url CLOB NOT NULL, has_free_shipping BOOLEAN NOT NULL, has_discount BOOLEAN NOT NULL, comment CLOB DEFAULT NULL, is_preferred BOOLEAN DEFAULT \'0\' NOT NULL)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE antibody');
        $this->addSql('DROP TABLE antibody_protein');
        $this->addSql('DROP TABLE antibody_antibody');
        $this->addSql('DROP TABLE antibody_dilution');
        $this->addSql('DROP TABLE box');
        $this->addSql('DROP TABLE box_entry');
        $this->addSql('DROP TABLE cell');
        $this->addSql('DROP TABLE cell_aliquote');
        $this->addSql('DROP TABLE chemical');
        $this->addSql('DROP TABLE culture_flask');
        $this->addSql('DROP TABLE experiment');
        $this->addSql('DROP TABLE experiment_protein');
        $this->addSql('DROP TABLE experiment_chemical');
        $this->addSql('DROP TABLE experiment_cell');
        $this->addSql('DROP TABLE experiment_type');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE morphology');
        $this->addSql('DROP TABLE organism');
        $this->addSql('DROP TABLE protein');
        $this->addSql('DROP TABLE rack');
        $this->addSql('DROP TABLE tissue');
        $this->addSql('DROP TABLE user_accounts');
        $this->addSql('DROP TABLE vendor');
    }
}
