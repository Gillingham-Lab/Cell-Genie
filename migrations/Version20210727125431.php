<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210727125431 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("antibody");
        $table->addColumn("validated_internally", "boolean", [
            "notnull" => true,
            "default" => false,
        ]);

        $table->addColumn("validated_externally", "boolean", [
            "notnull" => true,
            "default" => false,
        ]);

        $table->addColumn("external_reference", "string", [
            "length" => 255,
            "notnull" => false,
            "default" => null,
        ]);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("antibody");
        $table->dropColumn("validated_internally");
        $table->dropColumn("validated_externally");
        $table->dropColumn("external_reference");
    }
}
