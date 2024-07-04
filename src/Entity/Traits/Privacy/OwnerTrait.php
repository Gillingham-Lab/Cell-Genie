<?php
declare(strict_types=1);

namespace App\Entity\Traits\Privacy;

use App\Entity\DoctrineEntity\User\User;
use Doctrine\ORM\Mapping as ORM;

trait OwnerTrait
{
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?User $owner = null;

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}