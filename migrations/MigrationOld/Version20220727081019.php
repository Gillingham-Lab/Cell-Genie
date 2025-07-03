<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use ErrorException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Ulid;

final class Version20220727081019 extends AbstractMigration
{
    public function __construct(Connection $connection, LoggerInterface $logger)
    {
        $this->_logger = $logger;
        parent::__construct($connection, $logger);
    }

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        # Upgrade experiments

        # First, get a list of chemicals with id => ulid
        $result = $this->connection->createQueryBuilder()
            ->select("c.id, c.ulid")
            ->from("chemical", "c")
            ->fetchAllAssociative();

        $chemicalIdToUlid = [];
        foreach ($result as $row) {
            $chemicalIdToUlid[$row["id"]] = $row["ulid"];
        }

        # Then, get a list of proteins with id => ulid
        $result = $this->connection->createQueryBuilder()
            ->select("p.id, p.ulid")
            ->from("protein", "p")
            ->fetchAllAssociative();

        $proteinIdToUlid = [];
        foreach ($result as $row) {
            $proteinIdToUlid[$row["id"]] = $row["ulid"];
        }

        # Now onwards to experiments.
        # First, we get all experiments
        $result = $this->connection->createQueryBuilder()
            ->select("e.id")
            ->from("experiment", "e")
            ->fetchAllAssociative();

        foreach ($result as $row) {
            $this->_logger->debug("Updating experiment {$row['id']}");

            # Then, for each row, we get a list of general conditions.
            $generalConditions = $this->connection->createQueryBuilder()
                ->select("ec.id, ec.type")
                ->from("experimental_condition", "ec")
                ->where("ec.general = :general")
                ->andWhere("ec.experiment_id = :experimentId")
                ->setParameter("general", true, "boolean")
                ->setParameter("experimentId", $row["id"], "ulid")
                ->fetchAllAssociative();

            $generalConditionIdList = ["protein" => [], "chemical" => []];
            foreach ($generalConditions as $generalCondition) {
                if ($generalCondition["type"] === "protein") {
                    $generalConditionIdList["protein"][] = $generalCondition["id"];
                } elseif ($generalCondition["type"] === "chemical") {
                    $generalConditionIdList["chemical"][] = $generalCondition["id"];
                }
            }

            $this->_logger->debug("Found general conditions: " . (count($generalConditionIdList["protein"]) + count($generalConditionIdList["chemical"])));

            # And a list of the other conditions
            $conditions = $this->connection->createQueryBuilder()
                ->select("ec.id, ec.type")
                ->from("experimental_condition", "ec")
                ->where("ec.general = :general")
                ->andWhere("ec.experiment_id = :experimentId")
                ->setParameter("general", false, "boolean")
                ->setParameter("experimentId", $row["id"], "ulid")
                ->fetchAllAssociative();

            $conditionIdList = ["protein" => [], "chemical" => []];
            foreach ($conditions as $condition) {
                if ($condition["type"] === "protein") {
                    $conditionIdList["protein"][] = $condition["id"];
                } elseif ($condition["type"] === "chemical") {
                    $conditionIdList["chemical"][] = $condition["id"];
                }
            }

            $this->_logger->debug("Found conditions: " . (count($conditionIdList["protein"]) + count($conditionIdList["chemical"])));

            # And a list of the measurements
            $measurements = $this->connection->createQueryBuilder()
                ->select("em.id, em.type")
                ->from("experimental_measurement", "em")
                ->andWhere("em.experiment_id = :experimentId")
                ->setParameter("experimentId", $row["id"], "ulid")
                ->fetchAllAssociative();

            $measurementIdList = ["protein" => [], "chemical" => []];
            foreach ($measurements as $measurement) {
                if ($measurement["type"] === "protein") {
                    $measurementIdList["protein"][] = $measurement["id"];
                } elseif ($measurement["type"] === "chemical") {
                    $measurementIdList["chemical"][] = $measurement["id"];
                }
            }

            $this->_logger->debug("Found measurements: " . (count($measurementIdList["protein"]) + count($measurementIdList["chemical"])));

            # Now, we get all experimental runs for that experiment
            # A lot of work so far :(
            $experimentalRuns = $this->connection->createQueryBuilder()
                ->select("er.id, er.data")
                ->from("experimental_run", "er")
                ->where("er.experiment_id = :experimentId")
                ->setParameter("experimentId", $row["id"], "ulid")
                ->fetchAllAssociative();

            foreach ($experimentalRuns as $experimentalRun) {
                $this->_logger->debug("Updating run {$experimentalRun['id']}");

                # We must unserialize the data first.
                $data = unserialize($experimentalRun["data"]);

                # Now we must go through the data.
                # General, for protein
                foreach ($generalConditionIdList["protein"] as $uuid) {
                    $ulid58 = Ulid::fromRfc4122($uuid)->toBase58();

                    foreach ($data["conditions"] as $key => $condition) {
                        if ($condition["id"] === $ulid58) {
                            try {
                                $proteinUlid = Ulid::fromRfc4122($proteinIdToUlid[$condition["value"]])->toBase58();
                                $data["conditions"][$key]["value"] = $proteinUlid;
                                $this->_logger->debug("Updating protein {$condition['value']} to {$proteinUlid}");
                            } catch (ErrorException) {
                                $this->_logger->debug("Protein already updated {$condition['value']} or not found.");
                                continue;
                            }
                        }
                    }
                }

                # General, for chemical
                foreach ($generalConditionIdList["chemical"] as $uuid) {
                    $ulid58 = Ulid::fromRfc4122($uuid)->toBase58();

                    foreach ($data["conditions"] as $key => $condition) {
                        try {
                            if ($condition["id"] === $ulid58) {
                                $chemicalUlid = Ulid::fromRfc4122($chemicalIdToUlid[$condition["value"]])->toBase58();
                                $data["conditions"][$key]["value"] = $chemicalUlid;
                                $this->_logger->debug("Updating chemical {$condition['value']} to {$chemicalUlid}");
                            }
                        } catch (ErrorException) {
                            $this->_logger->debug("Chemical already updated {$condition['value']} or not found.");
                            continue;
                        }
                    }
                }

                # Update!
                $this->connection->createQueryBuilder()
                    ->update("experimental_run")
                    ->set("data", ":data")
                    ->where("id = :runId")
                    ->setParameter("data", $data, "array")
                    ->setParameter("runId", $experimentalRun["id"], "ulid")
                    ->executeQuery();

                # Now we must to this again, but for each runs condition and measurements.
                $experimentalRunWells = $this->connection->createQueryBuilder()
                    ->select("erw.id, erw.well_data")
                    ->from("experimental_run_well", "erw")
                    ->where("erw.experimental_run_id = :experimentalRUnId")
                    ->setParameter("experimentalRUnId", $experimentalRun["id"], "ulid")
                    ->fetchAllAssociative();

                foreach ($experimentalRunWells as $experimentalRunWell) {
                    $this->_logger->debug("Updating run well {$experimentalRunWell['id']}");

                    # We must unserialize the data first.
                    $wellData = unserialize($experimentalRunWell["well_data"]);

                    # Now we must go through the data.
                    # Condition, for protein
                    foreach ($conditionIdList["protein"] as $uuid) {
                        $ulid58 = Ulid::fromRfc4122($uuid)->toBase58();

                        foreach ($wellData["conditions"] as $key => $condition) {
                            try {
                                if ($condition["id"] === $ulid58) {
                                    $proteinUlid = Ulid::fromRfc4122($proteinIdToUlid[$condition["value"]])->toBase58();
                                    $wellData["conditions"][$key]["value"] = $proteinUlid;
                                    $this->_logger->debug("Updating protein {$condition['value']} to {$proteinUlid}");
                                }
                            } catch (ErrorException) {
                                $this->_logger->debug("Protein already updated {$condition['value']} or not found.");
                                continue;
                            }
                        }
                    }

                    # Condition, for chemical
                    foreach ($conditionIdList["chemical"] as $uuid) {
                        $ulid58 = Ulid::fromRfc4122($uuid)->toBase58();

                        foreach ($wellData["conditions"] as $key => $condition) {
                            try {
                                if ($condition["id"] === $ulid58) {
                                    $chemicalUlid = Ulid::fromRfc4122($chemicalIdToUlid[$condition["value"]])->toBase58();
                                    $wellData["conditions"][$key]["value"] = $chemicalUlid;
                                    $this->_logger->debug("Updating chemical {$condition['value']} to {$chemicalUlid}");
                                }
                            } catch (ErrorException) {
                                $this->_logger->debug("Chemical already updated {$condition['value']} or not found.");
                                continue;
                            }
                        }
                    }

                    # Measurement, for protein
                    foreach ($measurementIdList["protein"] as $uuid) {
                        $ulid58 = Ulid::fromRfc4122($uuid)->toBase58();

                        foreach ($wellData["measurements"] as $key => $measurement) {
                            try {
                                if ($measurement["id"] === $ulid58) {
                                    $proteinUlid = Ulid::fromRfc4122($proteinIdToUlid[$condition["value"]])->toBase58();
                                    $wellData["conditions"][$key]["value"] = $proteinUlid;
                                    $this->_logger->debug("Updating protein {$condition['value']} to {$proteinUlid}");
                                }
                            } catch (ErrorException) {
                                $this->_logger->debug("Protein already updated {$condition['value']} or not found.");
                                continue;
                            }
                        }
                    }

                    # Measurement, for chemical
                    foreach ($measurementIdList["chemical"] as $uuid) {
                        $ulid58 = Ulid::fromRfc4122($uuid)->toBase58();

                        foreach ($wellData["measurements"] as $key => $measurement) {
                            try {
                                if ($measurement["id"] === $ulid58) {
                                    $chemicalUlid = Ulid::fromRfc4122($chemicalIdToUlid[$condition["value"]])->toBase58();
                                    $wellData["conditions"][$key]["value"] = $chemicalUlid;
                                    $this->_logger->debug("Updating chemical {$condition['value']} to {$chemicalUlid}");
                                }
                            } catch (ErrorException) {
                                $this->_logger->debug("Chemical already updated {$condition['value']} or not found.");
                                continue;
                            }
                        }
                    }

                    # Now we need to update the data for this well.
                    $this->connection->createQueryBuilder()
                        ->update("experimental_run_well")
                        ->set("well_data", ":wellData")
                        ->where("id = :runId")
                        ->setParameter("wellData", $wellData, "array")
                        ->setParameter("runId", $experimentalRunWell["id"], "ulid")
                        ->executeQuery();
                }
            }
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
