<?php
declare(strict_types=1);

namespace App\Entity\Traits\Privacy;


use App\Entity\DoctrineEntity\User\UserGroup;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Groups;

trait GroupOwnerTrait
{
    #[Groups([
        "twig",
    ])]
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