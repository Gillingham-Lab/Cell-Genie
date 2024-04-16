<?php
declare(strict_types=1);

namespace DoctrineMigrations2024;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Dunglas\DoctrineJsonOdm\Type\JsonDocumentType;


final class Version20240416061033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a setting field to user accounts.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("user_accounts");
        $table->addColumn("settings", JsonDocumentType::NAME)
            ->setNotnull(false)
            ->setComment("(DC2Type:json_document)")
        ;
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("user_accounts");
        $table->dropColumn("settings");
    }
}
