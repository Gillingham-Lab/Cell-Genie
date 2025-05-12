<?php
declare(strict_types=1);

namespace App\Entity\Traits;

use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait ModifiedAtTrait
{
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\GreaterThanOrEqual("1970-01-01 00:00:00")]
    private ?DateTimeInterface $modifiedAt = null;

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateModifiedAt(): void
    {
        $this->setModifiedAt(new DateTime("now"));
    }

    public function getModifiedAt(): ?DateTimeInterface
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(?DateTimeInterface $modifiedAt): self
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }
}