<?php
declare(strict_types=1);

namespace DoctrineMigrations2023;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20230718063348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds additional fields to the user entity.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("user_accounts");

        $table->addColumn("personal_address", Types::STRING)->setLength(10)->setNotnull(false);
        $table->addColumn("title", Types::STRING)->setLength(20)->setNotnull(false);
        $table->addColumn("first_name", Types::STRING)->setLength(100)->setNotnull(false);
        $table->addColumn("last_name", Types::STRING)->setLength(100)->setNotnull(false);
        $table->addColumn("suffix", Types::STRING)->setLength(20)->setNotnull(false);
        $table->addColumn("phone_number", Types::STRING)->setLength(30)->setNotnull(false);
        $table->addColumn("office", Types::STRING)->setLength(30)->setNotnull(false);
        $table->addColumn("orcid", Types::STRING)->setLength(19)->setNotnull(false);
    }

    public function postUp(Schema $schema): void
    {
        $updateQuery = $this->connection->createQueryBuilder()
            ->update("user_accounts")
            ->set("first_name", "SUBSTRING(full_name, 1, STRPOS(full_name, ' '))")
            ->set("last_name", "SUBSTRING(full_name, STRPOS(full_name, ' ')+1)")
        ;

        $rows = $updateQuery->executeQuery();
        $rowCount = $rows->rowCount();

        $this->write(sprintf("Updated %d accounts to have first and lastname", $rowCount));
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("user_accounts");

        $table->dropColumn("personal_address");
        $table->dropColumn("title");
        $table->dropColumn("first_name");
        $table->dropColumn("last_name");
        $table->dropColumn("suffix");
        $table->dropColumn("phone_number");
        $table->dropColumn("office");
        $table->dropColumn("orcid");
    }
}
