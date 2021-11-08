<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class ExperimentalRunFormEntity extends AbstractExperimentalFormEntity
{
    private Experiment $experiment;
    private ?ExperimentalRun $experimentalRun = null;

    #[Assert\Length(min: 3, max: 255)]
    #[Assert\NotBlank]
    public ?string $name = null;

    #[Assert\Range(min: 1, max: 32000)]
    public ?int $numberOfWells = 1;

    public function __construct(Experiment $experiment)
    {
        $this->experiment = $experiment;

        $this->initConditionProperties($experiment);
    }

    public function __get(string $property): mixed
    {
        return $this->getConditionProperty($property) ?? null;
    }

    public function __set(string $property, mixed $value): void
    {
        $this->setConditionProperty($property, $value);
    }

    public function updateFromEntity(ExperimentalRun $experimentalRun)
    {
        if ($experimentalRun->getExperiment() !== $this->experiment) {
            throw new \InvalidArgumentException("Experiment of given ExperimentalRun must be the same as used in the constructor of this form entity.");
        }

        $this->experimentalRun = $experimentalRun;

        $this->name = $experimentalRun->getName();
        $this->numberOfWells = $experimentalRun->getNumberOfWells();

        // Unpack id and value from data array - title is not necessary
        $data = $experimentalRun->getData();

        if (isset($data["conditions"])) {
            foreach ($data["conditions"] as ["id" => $id, "value" => $value]) {
                $this->condition_data[$id] = $value;
            }
        }
    }

    public function getUpdatedEntity(): ExperimentalRun
    {
        if ($this->experimentalRun === null) {
            $experimentalRun = new ExperimentalRun();
            $this->experimentalRun = $experimentalRun;

            // This should never get changed
            $experimentalRun->setExperiment($this->experiment);
            $experimentalRun->setNumberOfWells($this->numberOfWells);

            // Create a new well entry for each entry.
            foreach(range(1, $this->numberOfWells) as $number) {
                $runWell = new ExperimentalRunWell();
                $runWell->setWellNumber($number);
                $runWell->setWellName("#{$number}");

                $experimentalRun->addWell($runWell);
            }
        } else {
            $experimentalRun = $this->experimentalRun;
        }

        $experimentalRun->setName($this->name);

        // Prepare data array
        $data = ["conditions" => []];
        /**
         * @var string $id
         * @var ExperimentalCondition $condition
         */
        foreach ($this->conditions as $id => $condition) {
            $datum = [
                "id" => $id,
                "title" => $condition->getTitle(),
                "value" => $this->condition_data[$id],
            ];

            $data["conditions"][] = $datum;
        }

        $experimentalRun->setData($data);

        return $experimentalRun;
    }

    public function validate(ExecutionContextInterface $context, $payload)
    {
        foreach ($this->conditions as $id => $condition) {
            switch ($condition->getType()) {
                case InputType::CHOICE_TYPE:
                    $choices = explode(",", $condition->getConfig());
                    $choices = array_map("trim", $choices);

                    if (array_search($this->condition_data[$id], $choices) === false) {
                        $choices_text = implode(",", $choices);

                        $context->buildViolation("Condition must be one of $choices_text, but {$this->condition_data[$id]} given.")
                            ->atPath("condition_{$id}")
                            ->addViolation()
                        ;
                    }
                    break;
            }
        }
    }

    public static function loadValidatorMetadata(ClassMetadata $classMetadata)
    {
        $classMetadata->addConstraint(new Assert\Callback("validate"));
    }
}