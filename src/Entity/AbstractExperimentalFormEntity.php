<?php
declare(strict_types=1);

namespace App\Entity;

class AbstractExperimentalFormEntity
{
    /** @var array<string, ExperimentalCondition> */
    protected array $conditions = [];

    /** @var array<string, mixed> */
    protected array $condition_data = [];

    /** @var array<string, ExperimentalMeasurement> */

    protected array $measurements = [];

    /** @var array<string, mixed> */
    protected array $measurement_data = [];

    protected function initConditionProperties(Experiment $experiment, bool $useGeneral = true): void
    {
        /** @var ExperimentalCondition $condition */
        foreach ($experiment->getConditions() as $condition) {
            if ($condition->isGeneral() === !$useGeneral) {
                continue;
            }

            $this->conditions[$condition->getId()->toBase58()] = $condition;
            $this->condition_data[$condition->getId()->toBase58()] = null;
        }
    }

    protected function initMeasurementProperties(Experiment $experiment): void
    {
        /** @var ExperimentalMeasurement $measurement */
        foreach ($experiment->getMeasurements() as $measurement) {
            $this->measurements[$measurement->getId()->toBase58()] = $measurement;
            $this->measurement_data[$measurement->getId()->toBase58()] = null;
        }
    }

    protected function getConditionProperty(string $property): mixed
    {
        if (str_starts_with($property, "condition_") === false) {
            return null;
        }

        $property_parts = explode("_", $property);
        $property_id = $property_parts[1];

        if (isset($this->conditions[$property_id])) {
            return $this->condition_data[$property_id];
        } else {
            return null;
        }
    }

    protected function getMeasurementProperty(string $property): mixed
    {
        if (str_starts_with($property, "measurement_") === false) {
            return null;
        }

        $property_parts = explode("_", $property);
        $property_id = $property_parts[1];

        if (isset($this->measurements[$property_id])) {
            return $this->measurement_data[$property_id];
        } else {
            return null;
        }
    }

    protected function setConditionProperty(string $property, mixed $value): void
    {
        if (str_starts_with($property, "condition_") === false) {
            return;
        }

        $property_parts = explode("_", $property);
        $property_id = $property_parts[1];

        if (isset($this->conditions[$property_id])) {
            $this->condition_data[$property_id] = $value;
        }
    }

    protected function setMeasurementProperty(string $property, mixed $value): void
    {
        if (str_starts_with($property, "measurement_") === false) {
            return;
        }

        $property_parts = explode("_", $property);
        $property_id = $property_parts[1];

        if (isset($this->measurements[$property_id])) {
            $this->measurement_data[$property_id] = $value;
        }
    }
}