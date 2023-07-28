<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Substance;

use App\Entity\Epitope;
use App\Entity\EpitopeProtein;
use App\Entity\Interface\PrivacyAwareInterface;
use App\Entity\Lot;
use App\Entity\Traits\Collections\HasUlidAttachmentsTrait;
use App\Entity\Traits\Fields\NameTrait;
use App\Entity\Traits\Fields\NewIdTrait;
use App\Entity\Traits\Privacy\PrivacyAwareTrait;
use App\Repository\Substance\SubstanceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SubstanceRepository::class)]
#[ORM\InheritanceType("JOINED")]
#[ORM\DiscriminatorColumn(name: "substance_type", type: "string")]
#[Gedmo\Loggable]
#[UniqueEntity("shortName")]
class Substance implements \JsonSerializable, PrivacyAwareInterface
{
    use NewIdTrait;
    use NameTrait;
    use HasUlidAttachmentsTrait;
    use PrivacyAwareTrait;

    #[ORM\ManyToMany(targetEntity: Lot::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    #[ORM\JoinTable(name: "substance_lots")]
    #[ORM\JoinColumn(name: "substance_ulid", referencedColumnName: "ulid")]
    #[ORM\InverseJoinColumn(name: "lot_id", referencedColumnName: "id", unique: true)]
    #[ORM\OrderBy(["lotNumber" => "ASC"])]
    #[Assert\Valid]
    private Collection $lots;

    #[ORM\ManyToMany(targetEntity: Epitope::class, mappedBy: "substances", cascade: ["persist"])]
    /*#[ORM\JoinTable(name: "substance_epitope")]
    #[ORM\JoinColumn(name: "substance_ulid", referencedColumnName: "ulid", nullable: false, onDelete: "CASCADE")]
    #[ORM\InverseJoinColumn(name: "epitope_id", referencedColumnName: "id")]*/
    private Collection $epitopes;

    public function __construct()
    {
        $this->lots = new ArrayCollection();
        $this->epitopes = new ArrayCollection();
        $this->attachments = new ArrayCollection();
    }

    public function jsonSerialize(): array
    {
        return [
            "ulid" => $this->getUlid(),
            "type" => static::class,
            "shortName" => $this->getShortName(),
            "longName" => $this->getLongName(),
            "number" => (method_exists($this, "getNumber") ? $this->getNumber() : null)
        ];
    }

    public function getLots(): Collection
    {
        return $this->lots;
    }

    public function addLot(Lot $lot): self
    {
        if (!$this->lots->contains($lot)) {
            $this->lots[] = $lot;
        }

        return $this;
    }

    public function removeLot(Lot $lot): self
    {
        $this->lots->removeElement($lot);
        return $this;
    }

    public function getEpitopes(): Collection
    {
        return $this->epitopes;
    }

    public function addEpitope(Epitope $epitope): self
    {
        var_dump("Add epitope");
        if (!$this->epitopes->contains($epitope)) {
            $this->epitopes[] = $epitope;
            $epitope->addSubstance($this);
        }

        return $this;
    }

    public function removeEpitope(Epitope $epitope): self
    {
        var_dump("Remove epitope");
        if ($this->epitopes->contains($epitope)) {
            $this->epitopes->removeElement($epitope);
            $epitope->removeSubstance($this);
        }
        return $this;
    }
}
