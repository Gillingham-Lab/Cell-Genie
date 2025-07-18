<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Cell;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class CellCultureSplittingEvent extends CellCultureEvent
{
    #[Groups([
        "twig",
    ])]
    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $splitting = null;

    #[Groups([
        "twig",
    ])]
    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $newFlask = null;

    public function getSplitting(): ?string
    {
        return $this->splitting;
    }

    public function setSplitting(string $splitting): self
    {
        $this->splitting = $splitting;

        return $this;
    }

    public function getNewFlask(): ?string
    {
        return $this->newFlask;
    }

    public function setNewFlask(string $newFlask): self
    {
        $this->newFlask = $newFlask;

        return $this;
    }

    #[Groups([
        "twig",
    ])]
    public function getCurrentPassage(): int
    {
        return $this->getCellCulture()->getCurrentPassage($this->getDate());
    }
}
