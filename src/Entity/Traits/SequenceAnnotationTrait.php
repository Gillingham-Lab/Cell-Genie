<?php
declare(strict_types=1);

namespace App\Entity\Traits;

use App\Entity\SequenceAnnotation;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

trait SequenceAnnotationTrait
{
    #[ORM\ManyToMany(targetEntity: SequenceAnnotation::class, cascade: ["persist", "remove"], fetch: "LAZY")]
    #[ORM\JoinTable]
    #[ORM\JoinColumn(name: "substance_id", referencedColumnName: "ulid", onDelete: "CASCADE")]
    #[ORM\InverseJoinColumn(name: "annotation_id", referencedColumnName: "id", unique: true, onDelete: "CASCADE")]
    #[ORM\OrderBy(["annotationStart" => "ASC", "annotationEnd" => "ASC", "annotationLabel" => "ASC"])]
    private Collection $sequenceAnnotations;

    public function getSequenceAnnotations(): Collection
    {
        return $this->sequenceAnnotations;
    }

    public function addSequenceAnnotation(SequenceAnnotation $annotation): self
    {
        if (!$this->sequenceAnnotations->contains($annotation)) {
            $this->sequenceAnnotations->add($annotation);
        }

        return $this;
    }

    public function removeSequenceAnnotation(SequenceAnnotation $annotation): self
    {
        if ($this->sequenceAnnotations->contains($annotation)) {
            $this->sequenceAnnotations->removeElement($annotation);
        }

        return $this;
    }
}