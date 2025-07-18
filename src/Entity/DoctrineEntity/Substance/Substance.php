<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Substance;

use App\Entity\DoctrineEntity\Epitope;
use App\Entity\DoctrineEntity\Lot;
use App\Entity\Interface\PrivacyAwareInterface;
use App\Entity\Traits\Collections\HasUlidAttachmentsTrait;
use App\Entity\Traits\Fields\NameTrait;
use App\Entity\Traits\Fields\NewIdTrait;
use App\Entity\Traits\Privacy\PrivacyAwareTrait;
use App\Genie\Enums\Availability;
use App\Repository\Substance\SubstanceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JsonSerializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SubstanceRepository::class)]
#[ORM\InheritanceType("JOINED")]
#[ORM\DiscriminatorColumn(name: "substance_type", type: "string")]
#[Gedmo\Loggable]
#[UniqueEntity("shortName")]
class Substance implements JsonSerializable, PrivacyAwareInterface
{
    use NewIdTrait;
    use NameTrait;
    use HasUlidAttachmentsTrait;
    use PrivacyAwareTrait;

    /** @var Collection<int, Lot> */
    #[ORM\ManyToMany(targetEntity: Lot::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    #[ORM\JoinTable(name: "substance_lots")]
    #[ORM\JoinColumn(name: "substance_ulid", referencedColumnName: "ulid")]
    #[ORM\InverseJoinColumn(name: "lot_id", referencedColumnName: "id", unique: true)]
    #[ORM\OrderBy(["number" => "ASC"])]
    #[Assert\Valid]
    private Collection $lots;

    /** @var Collection<int, Epitope> */
    #[ORM\ManyToMany(targetEntity: Epitope::class, mappedBy: "substances", cascade: ["persist"])]
    private Collection $epitopes;

    public function __construct()
    {
        $this->lots = new ArrayCollection();
        $this->epitopes = new ArrayCollection();
        $this->attachments = new ArrayCollection();
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            "ulid" => $this->getUlid(),
            "type" => static::class,
            "shortName" => $this->getShortName(),
            "longName" => $this->getLongName(),
            "number" => (method_exists($this, "getNumber") ? $this->getNumber() : null),
        ];
    }

    /** @return Collection<int, Lot> */
    public function getLots(): Collection
    {
        return $this->lots;
    }

    /** @return Collection<int, Lot> */
    public function getAvailableLots(): Collection
    {
        return $this->lots->matching((new Criteria())->where(new Comparison("availability", "=", Availability::Available->value)));
    }

    public function addLot(Lot $lot): static
    {
        if (!$this->lots->contains($lot)) {
            $this->lots[] = $lot;
        }

        return $this;
    }

    public function removeLot(Lot $lot): static
    {
        $this->lots->removeElement($lot);
        return $this;
    }

    /** @return Collection<int, Epitope> */
    public function getEpitopes(): Collection
    {
        return $this->epitopes;
    }

    public function addEpitope(Epitope $epitope): static
    {
        if (!$this->epitopes->contains($epitope)) {
            $this->epitopes[] = $epitope;
            $epitope->addSubstance($this);
        }

        return $this;
    }

    public function removeEpitope(Epitope $epitope): static
    {
        if ($this->epitopes->contains($epitope)) {
            $this->epitopes->removeElement($epitope);
            $epitope->removeSubstance($this);
        }
        return $this;
    }

    public function getCitation(?Lot $lot = null): string
    {
        $other = [
        ];

        if ($lot) {
            $other[] = "#Lot:{$lot->getLotNumber()}";
        }

        if (count($other)) {
            $other = implode(", ", $other);
            $other = " ($other)";
        } else {
            $other = "";
        }

        return "{$this->getLongName()}{$other}";
    }
}
