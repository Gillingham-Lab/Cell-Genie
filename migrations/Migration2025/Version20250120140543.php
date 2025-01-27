<?php
declare(strict_types=1);

namespace DoctrineMigrations2025;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Dunglas\DoctrineJsonOdm\Type\JsonDocumentType;

final class Version20250120140543 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a setting field to user groups.';
    }

    public function up(Schema $schema): void
    {
        $groupTable = $schema->getTable('user_group');
        $groupTable->addColumn("settings", JsonDocumentType::NAME)
            ->setNotnull(false)
            ->setComment("(DC2Type:json_document)")
        ;
    }

    public function down(Schema $schema): void
    {
        $groupTable = $schema->getTable('user_group');
        $groupTable->dropColumn("settings");
    }
}
