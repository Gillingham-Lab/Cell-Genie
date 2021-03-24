<?php

namespace App\Entity;

use App\Repository\RackRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RackRepository::class)
 */
class Rack
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $maxBoxes;

    /**
     * @ORM\OneToMany(targetEntity=Box::class, mappedBy="rack")
     */
    private Box $boxes;

    public function getId(): ?int
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

    public function getMaxBoxes(): ?int
    {
        return $this->maxBoxes;
    }

    public function setMaxBoxes(int $maxBoxes): self
    {
        $this->maxBoxes = $maxBoxes;

        return $this;
    }
}
