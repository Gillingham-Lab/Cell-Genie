<?php
declare(strict_types=1);

namespace App\Entity\Traits;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

trait TimestampTrait
{
    #[ORM\Column(type: "datetime", nullable: true)]
    private ?DateTimeInterface $createdAt = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?DateTimeInterface $modifiedAt = null;

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateTimestamps()
    {
        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt(new DateTime("now"));
        }

        $this->setModifiedAt(new DateTime("now"));
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