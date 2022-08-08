<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Cell;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity]
class CellCultureTestEvent extends CellCultureEvent
{
    const RESULTS = ['positive', 'negative', 'unclear'];

    #[ORM\Column(type: 'string', length: 30)]
    #[Assert\Choice(choices: self::RESULTS)]
    private ?string $result = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    private ?string $testType = null;

    #[ORM\Column(type: 'float')]
    #[Assert\Range(min: 0)]
    private ?float $supernatantAmount = null;

    public function getResult(): ?string
    {
        return $this->result;
    }

    public function setResult(string $result): self
    {
        $this->result = $result;

        return $this;
    }

    public function getTestType(): ?string
    {
        return $this->testType;
    }

    public function setTestType(string $testType): self
    {
        $this->testType = $testType;

        return $this;
    }

    public function getSupernatantAmount(): ?float
    {
        return $this->supernatantAmount;
    }

    public function setSupernatantAmount(float $supernatantAmount): self
    {
        $this->supernatantAmount = $supernatantAmount;

        return $this;
    }
}