<?php
declare(strict_types=1);

namespace App\Entity\Traits\Fields;

use App\Service\Doctrine\Generator\UlidGenerator;
use App\Service\Doctrine\Type\Ulid;
use Doctrine\ORM\Mapping as ORM;

trait IdTrait
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\Column(type: "ulid", unique: true)]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    private ?Ulid $id = null;

    private function generateId(): void
    {
        $this->id = new Ulid();
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }
}