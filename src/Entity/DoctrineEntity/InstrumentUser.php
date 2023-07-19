<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity;

use App\Entity\DoctrineEntity\User\User;
use App\Genie\Enums\InstrumentRole;
use App\Repository\Instrument\InstrumentUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InstrumentUserRepository::class)]
#[UniqueEntity(fields: ["instrument", "user"])]
class InstrumentUser
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Instrument::class, cascade: ["persist", "remove"], inversedBy: "users")]
    #[ORM\JoinColumn(name: "instrument_id", referencedColumnName: "id", onDelete: "CASCADE")]
    #[Assert\NotBlank]
    private ?Instrument $instrument = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class, cascade: ["persist", "remove"])]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", onDelete: "CASCADE")]
    #[Assert\NotBlank]
    private ?User $user = null;

    #[ORM\Column(type: "string", enumType: InstrumentRole::class, options: ["default" => InstrumentRole::Untrained])]
    #[Assert\NotBlank]
    private ?InstrumentRole $role = null;

    public function __toString(): string
    {
        return "{$this->user->getFullName()}: {$this->role->value}";
    }

    public function getInstrument(): ?Instrument
    {
        return $this->instrument;
    }

    public function setInstrument(?Instrument $instrument): self
    {
        $this->instrument = $instrument;

        if (!$instrument->getUsers()->contains($this)) {
            $instrument->addUser($this);
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getRole(): ?InstrumentRole
    {
        return $this->role;
    }

    public function setRole(?InstrumentRole $role): self
    {
        $this->role = $role;
        return $this;
    }
}