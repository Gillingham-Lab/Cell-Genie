<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\User;

use App\Entity\Param\ParamBag;
use App\Entity\Traits\CommentTrait;
use App\Entity\Traits\Fields\IdTrait;
use App\Entity\Traits\Fields\SettingsTrait;
use App\Entity\Traits\Privacy\PrivacyAwareTrait;
use App\Repository\User\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\OneToMany;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[Gedmo\Loggable()]
class Project
{
    use IdTrait;
    use SettingsTrait;
    use CommentTrait;
    use PrivacyAwareTrait;

    #[Groups([
        "twig",
    ])]
    #[Column(length: 20, nullable: false)]
    #[Gedmo\Versioned]
    private ?string $shortName;

    #[Groups([
        "twig",
    ])]
    #[Column(length: 255, nullable: false)]
    #[Gedmo\Versioned]
    private ?string $name;

    /** @var Collection<int, ProjectUser>  */
    #[Groups([
        "twig",
    ])]
    #[OneToMany(mappedBy: 'project', targetEntity: ProjectUser::class, cascade: ["persist", "remove"])]
    private Collection $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->settings = new ParamBag();
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(?string $shortName): static
    {
        $this->shortName = $shortName;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;
        return $this;
    }
}