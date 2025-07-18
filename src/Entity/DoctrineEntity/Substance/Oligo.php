<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Substance;

use App\Entity\Traits\CommentTrait;
use App\Entity\Traits\Fields\MolecularMassTrait;
use App\Entity\Traits\LabJournalTrait;
use App\Entity\Traits\SequenceTrait;
use App\Genie\Enums\OligoTypeEnum;
use App\Repository\Substance\OligoRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: OligoRepository::class)]
#[Gedmo\Loggable]
class Oligo extends Substance
{
    use CommentTrait;
    use MolecularMassTrait;
    use LabJournalTrait;
    use SequenceTrait;

    // Must be in 1/(mM*cm)
    #[ORM\Column(type: "float", nullable: true)]
    private ?float $extinctionCoefficient = null;

    #[ORM\Column(enumType: OligoTypeEnum::class, nullable: true)]
    private ?OligoTypeEnum $oligoTypeEnum = null;

    #[ORM\ManyToOne(targetEntity: Substance::class, cascade: ["persist", "remove"], fetch: "EAGER")]
    #[ORM\JoinColumn(referencedColumnName: "ulid", nullable: true, onDelete: "SET NULL")]
    private ?Substance $startConjugate = null;

    #[ORM\ManyToOne(targetEntity: Substance::class, cascade: ["persist", "remove"], fetch: "EAGER")]
    #[ORM\JoinColumn(referencedColumnName: "ulid", nullable: true, onDelete: "SET NULL")]
    private ?Substance $endConjugate = null;

    public function __construct()
    {
        parent::__construct();
    }

    public function getExtinctionCoefficient(): ?float
    {
        return $this->extinctionCoefficient;
    }

    public function setExtinctionCoefficient(?float $extinctionCoefficient): self
    {
        $this->extinctionCoefficient = $extinctionCoefficient;
        return $this;
    }

    public function getOligoTypeEnum(): ?OligoTypeEnum
    {
        return $this->oligoTypeEnum;
    }

    public function setOligoTypeEnum(?OligoTypeEnum $oligoTypeEnum): void
    {
        $this->oligoTypeEnum = $oligoTypeEnum;
    }

    public function getStartConjugate(): ?Substance
    {
        return $this->startConjugate;
    }

    public function setStartConjugate(?Substance $startConjugate): void
    {
        $this->startConjugate = $startConjugate;
    }

    public function getEndConjugate(): ?Substance
    {
        return $this->endConjugate;
    }

    public function setEndConjugate(?Substance $endConjugate): void
    {
        $this->endConjugate = $endConjugate;
    }
}
