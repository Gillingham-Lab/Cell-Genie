<?php

namespace App\Entity;

use App\Repository\AntibodyHostRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=AntibodyHostRepository::class)
 */
class AntibodyHost
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\Column(type="ulid", unique=True)
     * @ORM\CustomIdGenerator(class=UlidGenerator::class)
     */
    private ?Ulid $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Minimum length for host name is 3",
        maxMessage: "Maximum length for host name is 255"
    )]
    private ?string $name;

    public function __toString(): string
    {
        return "{$this->name}";
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
