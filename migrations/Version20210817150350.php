<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210817150350 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        /* $this->addSql('DROP SEQUENCE file_id_seq CASCADE');
        $this->addSql('CREATE TABLE file_blob (id UUID NOT NULL, content BYTEA NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN file_blob.id IS \'(DC2Type:ulid)\''); */
        $table = $schema->createTable("file_blob");
        $table->addColumn("id", "guid", ["notnull" => true])->setComment("DC2Type:ulid");
        $table->addColumn("content", "blob", ["notnull" => true]);
        $table->setPrimaryKey(["id"]);

        // $this->addSql('ALTER TABLE antibody ALTER vendor_pn TYPE VARCHAR(255)');
        $table = $schema->getTable("antibody");
        $table->getColumn("vendor_pn")->setType(Type::getType("VARCHAR"))->setLength(255);

        /*$this->addSql('ALTER TABLE file ADD file_blob_id UUID NOT NULL');
        $this->addSql('ALTER TABLE file ADD title VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE file ADD description TEXT NOT NULL');
        $this->addSql('ALTER TABLE file ADD uploaded_on TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE file ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE file ALTER id DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN file.file_blob_id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN file.id IS \'(DC2Type:ulid)\'');*/
        $table = $schema->getTable("file");
        $table->addColumn("file_blob_id", "guid", ["notnull" => true])->setComment("(DC2Type:ulid)");
        $table->addColumn("title", "string", ["notnull" => true])->setLength(255);
        $table->addColumn("description", "text", ["notnull" => true]);
        $table->addColumn("uploaded_on", "datetime", ["notnull" => false])->setDefault("null");
        $table->getColumn("id")
            ->setType(Type::getType("guid"))
            ->setComment("(DC2Type:ulid)")
            ->setDefault(null);

        /*$this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F3610B658379E FOREIGN KEY (file_blob_id) REFERENCES file_blob (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8C9F3610B658379E ON file (file_blob_id)');*/
        $table->addForeignKeyConstraint("file_blob", ["file_blob_id"], ["id"], options: [], constraintName: "FK_8C9F3610B658379E");
        $table->addUniqueIndex(["file_blob_id"], "UNIQ_8C9F3610B658379E");
    }

    public function down(Schema $schema): void
    {
        // Downgrade not possible
        $this->throwIrreversibleMigrationException();
    }
}
