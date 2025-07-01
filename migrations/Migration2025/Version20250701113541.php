<?php

declare(strict_types=1);

namespace DoctrineMigrations2025;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250701113541 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Removes old tables';
    }

    public function up(Schema $schema): void
    {
        $schema->getTable("experiment_cell")->removeForeignKey("fk_d078464fff444c8");
        $schema->getTable("experimental_measurement")->removeForeignKey("fk_aa3802e9ff444c8");
        $schema->getTable("experimental_run")->removeForeignKey("fk_30b5493e7e3c61f9");
        $schema->getTable("experimental_run")->removeForeignKey("fk_30b5493eff444c8");
        $schema->getTable("experiment_chemical")->removeForeignKey("fk_b8f4e4f2325508d6");
        $schema->getTable("experiment_chemical")->removeForeignKey("fk_b8f4e4f2ff444c8");
        $schema->getTable("experiment")->removeForeignKey("fk_136f58b27e3c61f9");
        $schema->getTable("experiment")->removeForeignKey("fk_136f58b2eb0f4b39");
        $schema->getTable("experimental_run_well")->removeForeignKey("fk_ad072c69d204bed5");
        $schema->getTable("experiment_protein")->removeForeignKey("fk_b6bb26189926e711");
        $schema->getTable("experiment_protein")->removeForeignKey("fk_b6bb2618ff444c8");
        $schema->getTable("experiment_type")->removeForeignKey("fk_97219684727aca70");
        $schema->getTable("experiment_type")->removeForeignKey("fk_97219684b03a8386");
        $schema->getTable("experimental_condition")->removeForeignKey("fk_6e25798cff444c8");

        $schema->dropTable("experiment_cell");
        $schema->dropTable("experimental_measurement");
        $schema->dropTable("experimental_run");
        $schema->dropTable("experiment_chemical");
        $schema->dropTable("experiment");
        $schema->dropTable("experimental_run_well");
        $schema->dropTable("experiment_protein");
        $schema->dropTable("experiment_type");
        $schema->dropTable("experimental_condition");

        $schema->getTable("instrument")->removeForeignKey("FK_3CBF69DD1A36181E");
        $schema->getTable("instrument")->addForeignKeyConstraint("file", ["visualisation_id"], ["id"], ["onDelete" => "SET NULL"], "FK_3CBF69DD1A36181E");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
