<?php
declare(strict_types=1);

namespace App\Entity\Traits;


use App\Entity\DoctrineEntity\User\UserGroup;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait GroupOwnerTrait
{
    #[ORM\ManyToOne(targetEntity: UserGroup::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?UserGroup $group = null;

    public function getGroup(): ?UserGroup
    {
        return $this->group;
    }

    public function setGroup(?UserGroup $group): self
    {
        $this->group = $group;

        return $this;
    }
}