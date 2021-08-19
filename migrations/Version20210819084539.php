<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210819084539 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // $this->addSql('CREATE TABLE antibody_lots (antibody_id INT NOT NULL, lot_id UUID NOT NULL, PRIMARY KEY(antibody_id, lot_id))');
        // $this->addSql('CREATE INDEX IDX_5C96DB8651162764 ON antibody_lots (antibody_id)');
        // $this->addSql('CREATE UNIQUE INDEX UNIQ_5C96DB86A8CBA5F7 ON antibody_lots (lot_id)');
        // $this->addSql('COMMENT ON COLUMN antibody_lots.lot_id IS \'(DC2Type:ulid)\'');
        $table = $schema->createTable("antibody_lots");
        $table->addColumn("antibody_id", "integer")
            ->setNotnull(True);
        $table->addColumn("lot_id", "guid")
            ->setNotnull(True)
            ->setComment("(DC2Type:ulid)")
        ;
        $table->setPrimaryKey(["antibody_id", "lot_id"]);
        $table->addIndex(["antibody_id"], "IDX_5C96DB8651162764");
        $table->addUniqueIndex(["lot_id"], "UNIQ_5C96DB86A8CBA5F7");

        // $this->addSql('CREATE TABLE lot (
        // +    id UUID NOT NULL,
        // +    bought_by_id UUID NOT NULL,
        // +    number VARCHAR(20) NOT NULL,
        // +    lot_number VARCHAR(50) NOT NULL,
        // +    bought_on TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
        // +    opened_on TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
        // +    amount VARCHAR(10) NOT NULL,
        // +    purity VARCHAR(15) NOT NULL,
        // +    aliquote_size VARCHAR(15) DEFAULT NULL,
        // +    number_of_aliquotes SMALLINT DEFAULT NULL,
        // +    comment TEXT DEFAULT NULL,
        // +    PRIMARY KEY(id)
        // + )');
        // $this->addSql('CREATE INDEX IDX_B81291BDEC6D6BA ON lot (bought_by_id)');
        // + $this->addSql('COMMENT ON COLUMN lot.id IS \'(DC2Type:ulid)\'');
        // + $this->addSql('COMMENT ON COLUMN lot.bought_by_id IS \'(DC2Type:ulid)\'');
        // $this->addSql('ALTER TABLE antibody_lots ADD CONSTRAINT FK_5C96DB8651162764 FOREIGN KEY (antibody_id) REFERENCES antibody (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        // $this->addSql('ALTER TABLE antibody_lots ADD CONSTRAINT FK_5C96DB86A8CBA5F7 FOREIGN KEY (lot_id) REFERENCES lot (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        // $this->addSql('ALTER TABLE lot ADD CONSTRAINT FK_B81291BDEC6D6BA FOREIGN KEY (bought_by_id) REFERENCES user_accounts (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $table = $schema->createTable("lot");
        $table->addColumn("id", "guid")
            ->setNotnull(True)
            ->setComment("(DC2Type:ulid)")
        ;
        $table->setPrimaryKey(["id"]);
        $table->addColumn("bought_by_id", "guid")
            ->setNotnull(True)
            ->setComment("(DC2Type:ulid)")
        ;
        $table->addColumn("number", "string")
            ->setLength(20)
            ->setNotnull(True)
        ;
        $table->addColumn("lot_number", "string")
            ->setLength(50)
            ->setNotnull(True)
        ;
        $table->addColumn("bought_on", "datetime")
            ->setNotnull(True)
        ;
        $table->addColumn("opened_on", "datetime")
            ->setNotnull(True)
        ;
        $table->addColumn("amount", "string")
            ->setLength(10)
            ->setNotnull(True)
        ;
        $table->addColumn("purity", "string")
            ->setLength(15)
            ->setNotnull(True)
        ;
        $table->addColumn("aliquote_size", "string")
            ->setLength(15)
            ->setNotnull(False)
            ->setDefault(null)
        ;
        $table->addColumn("number_of_aliquotes", "smallint")
            ->setNotnull(False)
            ->setDefault(null)
        ;
        $table->addColumn("comment", "text")
            ->setNotnull(False)
            ->setDefault(null)
        ;

        $table->addIndex(["bought_by_id"], "IDX_B81291BDEC6D6BA");

        $table = $schema->getTable("antibody_lots");
        $table->addForeignKeyConstraint("antibody", ["antibody_id"],  ["id"], constraintName: "FK_5C96DB8651162764");
        $table->addForeignKeyConstraint("lot", ["lot_id"],  ["id"], constraintName: "FK_5C96DB86A8CBA5F7");

        $table = $schema->getTable("lot");
        $table->addForeignKeyConstraint("user_accounts", ["bought_by_id"],  ["id"], constraintName: "FK_B81291BDEC6D6BA");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->getTable("antibody_lots")->removeForeignKey("FK_5C96DB86A8CBA5F7");
        $schema->getTable("antibody_lots")->removeForeignKey("FK_5C96DB8651162764");
        $schema->getTable("lot")->removeForeignKey("FK_B81291BDEC6D6BA");
        $schema->dropTable("antibody_lots");
        $schema->dropTable("lot");
    }
}
