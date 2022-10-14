<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221014122926 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Moves antibody_vendor_documentation_files to substance_files.';
    }

    public function up(Schema $schema): void
    {
        $qb = $this->connection->createQueryBuilder();

        $result = $qb->select("a.file_id, a.antibody_ulid")
            ->from("antibody_vendor_documentation_files", "a")
            ->fetchAllAssociative();

        foreach ($result as $row) {
            $this->connection->createQueryBuilder()
                ->insert("substance_file")
                ->setValue("substance_ulid", ":substance")
                ->setValue("file_id", ":file")
                ->setParameter("substance", $row["antibody_ulid"], "ulid")
                ->setParameter("file", $row["file_id"], "ulid")
                ->executeQuery();
        }
    }

    public function down(Schema $schema): void
    {
    }
}
