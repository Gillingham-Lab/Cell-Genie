<?php
declare(strict_types=1);

namespace App\Entity\Traits;

use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait CreatedAtTrait
{
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\GreaterThanOrEqual("1970-01-01 00:00:00")]
    private ?DateTimeInterface $createdAt = null;

    #[ORM\PrePersist]
    public function updateCreatedAt(): void
    {
        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt(new DateTime("now"));
        }
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
