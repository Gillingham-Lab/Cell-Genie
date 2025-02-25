<?php
declare(strict_types=1);

namespace App\Entity\Traits\Collections;

use App\Entity\DoctrineEntity\File\File;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait HasUlidAttachmentsTrait
{
    use HasAttachmentsTrait;

    /** @var Collection<int, File> */
    #[ORM\ManyToMany(targetEntity: File::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    #[ORM\JoinTable]
    #[ORM\JoinColumn(referencedColumnName: "ulid")]
    #[ORM\InverseJoinColumn(name: "file_id", referencedColumnName: "id", unique: true, onDelete: "CASCADE")]
    #[ORM\OrderBy(["title" => "ASC"])]
    #[Assert\Valid]
    private Collection $attachments;
}