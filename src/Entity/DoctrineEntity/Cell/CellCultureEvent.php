<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Cell;

use App\Entity\DoctrineEntity\User\User;
use App\Entity\Interface\PrivacyAwareInterface;
use App\Entity\Traits\Fields\IdTrait;
use App\Entity\Traits\Privacy\GroupOwnerTrait;
use App\Entity\Traits\Privacy\PrivacyLevelTrait;
use App\Entity\Traits\UnversionedShortNameTrait;
use App\Repository\Cell\CellCultureEventRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CellCultureEventRepository::class)]
#[ORM\InheritanceType("JOINED")]
#[ORM\DiscriminatorColumn(name: "event_type", type: "string")]
class CellCultureEvent implements PrivacyAwareInterface
{
    use IdTrait;
    use UnversionedShortNameTrait;
    use GroupOwnerTrait;
    use PrivacyLevelTrait;

    #[ORM\ManyToOne(targetEntity: CellCulture::class, inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?CellCulture $cellCulture = null;

    #[Groups([
        "twig",
    ])]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?User $owner = null;

    #[Groups([
        "twig",
    ])]
    #[ORM\Column(type: 'date', nullable: false)]
    #[Assert\NotBlank]
    private ?DateTimeInterface $date;

    #[Groups([
        "twig",
    ])]
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[Groups([
        "twig",
    ])]
    public function getEventType(): string
    {
        return static::class;
    }

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
