<?php
declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

class ExperimentalRunWellFormEntity extends AbstractExperimentalFormEntity
{
    private Experiment $experiment;
    private ?ExperimentalRun $experimentalRun = null;

    private Ulid $id;

    private ?int $wellNumber = null;

    #[Assert\Length(min: 1, max: 30)]
    #[Assert\NotBlank]
    public ?string $wellName = null;

    public bool $isExternalStandard = false;

    public function __construct(Experiment $experiment)
    {
        $this->experiment = $experiment;

        $this->initConditionProperties($experiment, false);
        $this->initMeasurementProperties($experiment);
    }

    public function __toString(): string
    {
        return "({$this->wellNumber}) {$this->wellName}";
    }

    public function __get(string $property): mixed
    {
        $a = $this->getConditionProperty($property);

        if ($a === null) {
            $a = $this->getMeasurementProperty($property);
        }

        return $a;
    }

    public function __set(string $property, mixed $value): void
    {
        $this->setConditionProperty($property, $value);
        $this->setMeasurementProperty($property, $value);
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getWellNumber(): ?int
    {
        return $this->wellNumber;
    }

    public function updateFromEntity(ExperimentalRunWell $well): void
    {
        $this->id = $well->getId();
        $this->wellName = $well->getWellName();
        $this->wellNumber = $well->getWellNumber();
        $this->isExternalStandard = $well->isExternalStandard();

        // Retrieve data fields
        $data = $well->getWellData();

        if (isset($data["conditions"])) {
            foreach ($data["conditions"] as ["id" => $id, "value" => $value]) {
                $this->condition_data[$id] = $value;
            }
        }

        if (isset($data["measurements"])) {
            foreach ($data["measurements"] as ["id" => $id, "value" => $value]) {
                $this->measurement_data[$id] = $value;
            }
        }
    }

    public function updateEntity(ExperimentalRunWell $well): void
    {
        $well->setWellName($this->wellName);
        $well->setIsExternalStandard($this->isExternalStandard);

        // Prepare data array
        $data = [
            "conditions" => [],
            "measurements" => [],
        ];

        /**
         * @var string $id
         * @var ExperimentalCondition $condition
         */
        foreach ($this->conditions as $id => $condition) {
            $datum = [
                "id" => $id,
                "title" => $condition->getTitle(),
                "value" => $this->condition_data[$id],
                "type" => $condition->getType(),
            ];

            $data["conditions"][] = $datum;
        }

        /**
         * @var string $id
         * @var ExperimentalMeasurement $measurement
         */
        foreach ($this->measurements as $id => $measurement) {
            $datum = [
                "id" => $id,
                "title" => $measurement->getTitle(),
                "value" => $this->measurement_data[$id],
                "type" => $measurement->getType(),
            ];

            $data["measurements"][] = $datum;
        }

        $well->setWellData($data);
    }
}