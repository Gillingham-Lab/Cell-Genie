<?php
declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\Fields\IdTrait;
use App\Repository\ExperimentalRunWellRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExperimentalRunWellRepository::class)]
#[UniqueEntity(fields: ["wellNumber", "experimentalRun"], message: "Each well number can only be used once.")]
#[ORM\UniqueConstraint(fields: ["experimentalRun", "wellNumber"])]
class ExperimentalRunWell
{
    use IdTrait;

    #[ORM\ManyToOne(targetEntity: ExperimentalRun::class, inversedBy: "wells")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?ExperimentalRun $experimentalRun = null;

    #[ORM\Column(type: "integer", nullable: false)]
    #[Assert\NotBlank]
    private ?int $wellNumber = null;

    #[ORM\Column(type: "string", nullable: false)]
    #[Assert\Length(min: 1, max: 30)]
    #[Assert\NotBlank]
    private ?string $wellName = null;

    #[ORM\Column(type: "array", nullable: false, options: ["default" => "a:0:{}"])]
    private array $wellData = [];

    #[ORM\Column(type: "boolean", nullable: false, options: ["default" => false])]
    private bool $isExternalStandard = false;

    public function getExperimentalRun(): ?ExperimentalRun
    {
        return $this->experimentalRun;
    }

    public function setExperimentalRun(?ExperimentalRun $experimentalRun): self
    {
        $this->experimentalRun = $experimentalRun;

        return $this;
    }

    public function getWellNumber(): ?int
    {
        return $this->wellNumber;
    }

    public function setWellNumber(int $wellNumber): self
    {
        $this->wellNumber = $wellNumber;

        return $this;
    }

    public function getWellName(): ?string
    {
        return $this->wellName;
    }

    public function setWellName(string $wellName): self
    {
        $this->wellName = $wellName;

        return $this;
    }

    public function getWellData(): array
    {
        return $this->wellData;
    }

    public function setWellData(array $wellData = []): self
    {
        $this->wellData = $wellData;

        return $this;
    }

    private function getWellDatum(string $type, string|Ulid $idBase58) {
        if ($idBase58 instanceof Ulid) {
            $idBase58 = $idBase58->toBase58();
        }

        if (!isset($this->wellData[$type])) {
            return null;
        }

        if (isset($this->wellData[$type][$idBase58])) {
            return $this->wellData[$type][$idBase58]["value"];
        } else {
            foreach ($this->wellData[$type] as $datum) {
                if ($datum["id"] === $idBase58) {
                    return $datum["value"];
                }
            }
        }

        return null;
    }

    public function getWellConditionDatum(string|Ulid $idBase58): mixed
    {
        return $this->getWellDatum("conditions", $idBase58);
    }

    public function getWellMeasurementDatum(string|Ulid $idBase58): mixed
    {
        return $this->getWellDatum("measurements", $idBase58);
    }

    public function isExternalStandard(): bool
    {
        return $this->isExternalStandard;
    }

    public function setIsExternalStandard(bool $isExternalStandard = true): self
    {
        $this->isExternalStandard = $isExternalStandard;

        return $this;
    }
}