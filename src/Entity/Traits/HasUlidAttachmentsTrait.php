<?php
declare(strict_types=1);

namespace App\Entity\Traits;

use App\Entity\File;
use App\Form\AdminCrud\DocumentationType;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Symfony\Component\Validator\Constraints as Assert;

trait HasUlidAttachmentsTrait
{
    use HasAttachmentsTrait;
    #[ORM\ManyToMany(targetEntity: File::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    #[ORM\JoinTable]
    #[ORM\JoinColumn(referencedColumnName: "ulid")]
    #[ORM\InverseJoinColumn(name: "file_id", referencedColumnName: "id", unique: true, onDelete: "CASCADE")]
    #[ORM\OrderBy(["title" => "ASC"])]
    #[Assert\Valid]
    private Collection $attachments;
}