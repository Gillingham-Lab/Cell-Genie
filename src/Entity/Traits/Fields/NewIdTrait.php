<?php
declare(strict_types=1);

namespace App\Entity\Traits\Fields;

use App\Service\Doctrine\Generator\UlidGenerator;
use App\Service\Doctrine\Type\Ulid;
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