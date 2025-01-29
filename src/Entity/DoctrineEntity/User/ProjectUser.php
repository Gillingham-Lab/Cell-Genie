<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\User;

use App\Entity\Traits\Fields\IdTrait;
use App\Repository\User\ProjectUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: ProjectUserRepository::class)]
#[Gedmo\Loggable()]
#[ORM\UniqueConstraint(columns: ["project_id", "user_id"])]
class ProjectUser
{
    use IdTrait;

    #[ManyToOne(targetEntity: Project::class, cascade: ["persist", "remove"], inversedBy: "users")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Project $project = null;

    #[ManyToOne(targetEntity: User::class, cascade: ["persist", "remove"], inversedBy: "projects")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?User $user = null;

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }
}