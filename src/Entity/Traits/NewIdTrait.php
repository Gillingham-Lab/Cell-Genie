<?php
declare(strict_types=1);

namespace App\Entity\Traits;

use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Uid\Ulid;
use Doctrine\ORM\Mapping as ORM;

trait NewIdTrait
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\Column(type: "ulid", unique: true)]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    private ?Ulid $ulid = null;

    private function generateUlid(): void
    {
        $this->ulid = new Ulid();
    }

    public function getUlid(): ?Ulid
    {
        return $this->ulid;
    }
}