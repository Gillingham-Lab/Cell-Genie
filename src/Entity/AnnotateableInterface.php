<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;

interface AnnotateableInterface
{
    public function getSequence(): ?string;
    public function setSequence(?string $sequence): self;

    /** @return Collection<int, SequenceAnnotation> */
    public function getSequenceAnnotations(): Collection;
    public function addSequenceAnnotation(SequenceAnnotation $annotation): self;
    public function removeSequenceAnnotation(SequenceAnnotation $annotation): self;
}