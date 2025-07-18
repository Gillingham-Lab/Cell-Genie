<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity;

use App\Entity\Traits\Collections\HasAttachmentsTrait;
use App\Entity\Traits\CommentTrait;
use App\Entity\Traits\Fields\IdTrait;
use App\Entity\Traits\Fields\LongNameTrait;
use App\Entity\Traits\Fields\VisualisationTrait;
use App\Entity\Traits\Privacy\PrivacyAwareTrait;
use App\Repository\ResourceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ResourceRepository::class)]
#[Gedmo\Loggable]
class Resource
{
    use IdTrait;
    use CommentTrait;
    use VisualisationTrait;
    use HasAttachmentsTrait;
    use LongNameTrait;
    use PrivacyAwareTrait;

    #[ORM\Column]
    public string $category;

    #[ORM\Column]
    #[Assert\Url]
    public string $url;

    public function __construct()
    {
        $this->attachments = new ArrayCollection();
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }
}
