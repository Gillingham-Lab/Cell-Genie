<?php
declare(strict_types=1);

namespace App\Entity\Traits\Collections;

use App\Entity\File;
use App\Form\AdminCrud\DocumentationType;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Symfony\Component\Validator\Constraints as Assert;

trait HasAttachmentsTrait
{
    /** @var Collection<int, File> */
    #[ORM\ManyToMany(targetEntity: File::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    #[ORM\JoinTable]
    #[ORM\InverseJoinColumn(name: "file_id", referencedColumnName: "id", unique: true, onDelete: "CASCADE")]
    #[ORM\OrderBy(["title" => "ASC"])]
    #[Assert\Valid]
    private Collection $attachments;

    public static function attachmentCrudFields(): array
    {
        return [
            CollectionField::new("attachments", "Attachments")
                ->setHelp("Add file attachments to provide complete documentation.")
                ->setEntryType(DocumentationType::class)
                ->setEntryIsComplex(true)
                ->hideOnIndex()
                ->allowDelete(True),
        ];
    }

    /**
     * @return Collection<int, File>
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function addAttachment(File $vendorDocumentation): self
    {
        if (!$this->attachments->contains($vendorDocumentation)) {
            $this->attachments[] = $vendorDocumentation;
        }

        return $this;
    }

    public function removeAttachment(File $vendorDocumentation): self
    {
        $this->attachments->removeElement($vendorDocumentation);

        return $this;
    }
}