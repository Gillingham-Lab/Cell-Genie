<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Cell;

use App\Entity\Traits\IdTrait;
use App\Entity\Traits\UnversionedShortNameTrait;
use App\Entity\User;
use App\Repository\Cell\CellCultureEventRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CellCultureEventRepository::class)]
#[ORM\InheritanceType("JOINED")]
#[ORM\DiscriminatorColumn(name: "event_type", type: "string")]
class CellCultureEvent
{
    use IdTrait;
    use UnversionedShortNameTrait;

    #[ORM\ManyToOne(targetEntity: CellCulture::class, inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?CellCulture $cellCulture = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?User $owner = null;

    #[ORM\Column(type: 'date', nullable: false)]
    #[Assert\NotBlank]
    private ?DateTimeInterface $date;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    public function __construct()
    {
        $this->date = new DateTime("today");
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCellCulture(): ?CellCulture
    {
        return $this->cellCulture;
    }

    public function setCellCulture(?CellCulture $cellCulture): self
    {
        $this->cellCulture = $cellCulture;

        return $this;
    }
}
