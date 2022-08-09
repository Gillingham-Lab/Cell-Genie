<?php
declare(strict_types=1);

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait NumberTrait
{
    #[ORM\Column(type: "string", length: 10, nullable: true, options: ["default" => "???"])]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 1,
        max: 10,
    )]
    private ?string $number = "???";

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }
}
