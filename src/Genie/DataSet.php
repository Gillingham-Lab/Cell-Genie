<?php
declare(strict_types=1);

namespace App\Genie;

use App\Entity\Experiment;
use App\Entity\ExperimentalCondition;
use App\Entity\ExperimentalMeasurement;
use App\Entity\ExperimentalRun;
use App\Entity\ExperimentalRunWell;
use App\Service\Doctrine\Type\Ulid;
use Doctrine\Common\Collections\Collection;

class DataSet
{
    /** @var array<string, ExperimentalCondition> */
    private array $generalConditions = [];
    /** @var array<string, ExperimentalCondition> */
    private array $conditions = [];
    /** @var array<string, ExperimentalMeasurement> */
    private array $measurements = [];
    /** @var array<string, Ulid|string> */
    private array $internalStandardMeasurements = [];

    public function __construct(
        private Experiment $experiment,
    ) {

        // Get conditions
        /** @var ExperimentalCondition $condition */
        foreach ($this->experiment->getConditions() as $condition) {
            if ($condition->isGeneral()) {
                $this->generalConditions[$condition->getId()->toBase58()] = $condition;
            } else {
                $this->conditions[$condition->getId()->toBase58()] = $condition;
            }
        }

        // Get measurements
        /** @var ExperimentalMeasurement $measurement */
        foreach ($this->experiment->getMeasurements() as $measurement) {
            $this->measurements[$measurement->getId()->toBase58()] = $measurement;

            if ($measurement->isInternalStandard()) {
                $this->internalStandardMeasurements[] = $measurement->getId()->toBase58();
            }
        }
    }

    /**
     * @return string[]
     */
    protected function makeTableHeader(bool $includeRunDate = false): array
    {
        $h = ["Nr", "Name", "ESTD"];

        if ($includeRunDate) {
            $h = [...$h, "Created", "Modified"];
        }

        foreach ($this->generalConditions as $conditionKey => $condition) {
            $h[] = $condition->getTitle();
        }

        foreach ($this->conditions as $conditionKey => $condition) {
            $h[] = $condition->getTitle();
        }

        foreach ($this->measurements as $measurementKey => $measurement) {
            if ($measurement->isInternalStandard()) {
                $h[] = $measurement->getTitle() . " (ISTD)";
            } else {
                $h[] = $measurement->getTitle();
            }
        }

        return $h;
    }

    /**
     * @return array<string|string[]>
     */
    public function allToArray(bool $header = true, bool $normalise = false, bool $comments = true, bool $includeRunDate = true): array
    {
        if ($comments) {
            $table = [
                "#Experiment: {$this->experiment->getName()}",
                "#Experiment ID: {$this->experiment->getId()->toBase58()}",
                "#Experiment Owner: {$this->experiment->getOwner()}",
                "#Created: {$this->experiment->getCreatedAt()->format('Y-m-d H:i:s')}",
                "#Modified: {$this->experiment->getModifiedAt()->format('Y-m-d H:i:s')}",
            ];
        } else {
            $table = [];
        }

        if ($header) {
            $table[] = ["run id", "run name", ...$this->makeTableHeader($includeRunDate)];
        }

        foreach ($this->experiment->getExperimentalRuns() as $experimentalRun) {
            $runs = $this->runToArray($experimentalRun, header: false, normalise: $normalise, comments: false, includeRunDate: true);
            $runs = array_map(fn($x) => [$experimentalRun->getId()->toBase58(), $experimentalRun->getName(), ...$x], $runs);

            $table = [...$table, ...$runs];
        }

        return $table;
    }

    /**
     * @return array<string|string[]>
     */
    public function runToArray(
        ExperimentalRun $experimentalRun,
        bool $header = false,
        bool $normalise = false,
        bool $comments = false,
        bool $includeRunDate = false,
    ): array {
        if ($experimentalRun->getExperiment() !== $this->experiment) {
            throw new \InvalidArgumentException("Experimental run experiment must be equal to the experiment used for this dataset.");
        }

        if ($comments) {
            $table = [
                "#Experiment: {$this->experiment->getName()}",
                "#Experiment ID: {$this->experiment->getId()->toBase58()}",
                "#Run name: {$experimentalRun->getName()}",
                "#Run id: {$experimentalRun->getId()}",
                "#Owner: {$experimentalRun->getOwner()}",
                "#Created: {$experimentalRun->getCreatedAt()->format('Y-m-d H:i:s')}",
                "#Modified: {$experimentalRun->getModifiedAt()->format('Y-m-d H:i:s')}",
            ];
        } else {
            $table = [];
        }

        if ($header) {
            $table[] = $this->makeTableHeader($includeRunDate);
        }

        $wells = $experimentalRun->getWells();
        $runData = $experimentalRun->getData();

        $standardRows = [];
        $rows = [];
        $internalStandards = [];

        // Precache rows to reference standards later
        foreach ($wells as $well) {
            $wellId = $well->getId()->toBase58();

            if ($well->isExternalStandard()) {
                $standardRows[] = $wellId;
            }

            $rows[$wellId] = $well;

            // get columns with internal standard
            $internalFactor = [];
            foreach ($this->internalStandardMeasurements as $internalStandardId) {
                $_factor = $well->getWellMeasurementDatum($internalStandardId);

                // only add if its numeric - this is skipped if its null, for example.
                if (is_numeric($_factor)) {
                    $internalFactor[] = (float)$_factor;
                }
            }

            // If there is at least one, calculate average and save with well id.
            if (count($internalFactor) > 0) {
                $internalStandards[$wellId] = array_sum($internalFactor) / count($internalFactor);
            } else {
                $internalStandards[$wellId] = 1;
            }
        }

        // Calculate external standards (one for each measurement)
        $externalStandards = [];

        foreach ($this->measurements as $measurementId => $measurement) {
            // preinit array
            $externalFactor = [];

            // collect values from external standards
            foreach ($standardRows as $wellId) {
                $standardWell = $rows[$wellId];

                $value = $standardWell->getWellMeasurementDatum($measurementId);

                // Only add if value has been found
                if (is_numeric($value)) {
                    // Normalize with internal standard
                    $externalFactor[] = $value/$internalStandards[$wellId];
                }
            }

            // Calculate average and save with measurement id
            if (count($externalFactor) > 0) {
                $externalStandards[$measurementId] = array_sum($externalFactor) / count($externalFactor);
            } else {
                $externalStandards[$measurementId] = 1;
            }
        }

        // Prepare data
        foreach ($wells as $well) {
            $wellId = $well->getId()->toBase58();
            $row = [
                $well->getWellNumber(),
                $well->getWellName(),
                $well->isExternalStandard() ? "yes" : "no",
            ];

            if ($includeRunDate) {
                $row = [
                    ...$row,
                    $experimentalRun->getCreatedAt()->format('Y-m-d H:i:s'),
                    $experimentalRun->getModifiedAt()->format('Y-m-d H:i:s'),
                ];
            }

            foreach (array_keys($this->generalConditions) as $generalConditionKey) {
                $value = $experimentalRun->getConditionDatum($generalConditionKey) ?? "NaN";

                if ($value instanceof Ulid) {
                    $value = $value->toBase58();
                }

                $row[] = $value;
            }

            foreach (array_keys($this->conditions) as $conditionKey) {
                $value = $well->getWellConditionDatum($conditionKey) ?? "NaN";

                if ($value instanceof Ulid) {
                    $value = $value->toBase58();
                }

                $row[] = $value;
            }

            foreach (array_keys($this->measurements) as $measurementKey) {
                $value = $well->getWellMeasurementDatum($measurementKey);

                // Only normalize numerical values - if required.
                if (is_numeric($value) and $normalise) {
                    $row[] = $value / $internalStandards[$wellId] / $externalStandards[$measurementKey];
                } elseif (is_bool($value)) {
                    $row[] = $value ? "yes" : "no";
                } elseif ($value instanceof Ulid) {
                    $row[] = $value->toBase58();
                }else {
                    $row[] = $value ?? "NaN";
                }
            }

            $table[] = $row;
        }

        return $table;
    }
}