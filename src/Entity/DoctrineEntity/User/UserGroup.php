<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\User;

use App\Entity\Traits\Fields\IdTrait;
use App\Entity\Traits\Fields\ShortNameTrait;
use App\Repository\Substance\UserGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Loggable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: UserGroupRepository::class)]
#[ORM\Table]
#[ORM\UniqueConstraint(fields: ["shortName"])]
#[UniqueEntity(fields: ["shortName"], message: "This name is already in use.")]
#[Loggable]
class UserGroup
{
    use IdTrait;
    use ShortNameTrait;

    #[ORM\OneToMany(mappedBy: 'group', targetEntity: User::class)]
    private Collection $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getShortName();
    }

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }

        return $this;
    }
}