<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Substance;

use App\Entity\Lot;
use App\Entity\Traits\NameTrait;
use App\Entity\Traits\NewIdTrait;
use App\Repository\Substance\SubstanceRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SubstanceRepository::class)]
#[ORM\InheritanceType("JOINED")]
#[ORM\DiscriminatorColumn(name: "substance_type", type: "string")]
#[Gedmo\Loggable]
class Substance
{
    use NewIdTrait;
    use NameTrait;

    #[ORM\ManyToMany(targetEntity: Lot::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    #[ORM\JoinTable(name: "substance_lots")]
    #[ORM\JoinColumn(name: "substance_ulid", referencedColumnName: "ulid")]
    #[ORM\InverseJoinColumn(name: "lot_id", referencedColumnName: "id", unique: true)]
    #[ORM\OrderBy(["lotNumber" => "ASC"])]
    #[Assert\Valid]
    private Collection $lots;

    /**
     * @return Collection<int, Lot>
     */
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
}
