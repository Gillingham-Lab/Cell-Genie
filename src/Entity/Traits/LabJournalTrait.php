<?php
declare(strict_types=1);

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

trait LabJournalTrait
{
    #[ORM\Column(type: "text", nullable: true)]
    #[Assert\Url]
    #[Gedmo\Versioned]
    private ?string $labjournal = null;

    public function getLabjournal(): ?string
    {
        return $this->labjournal;
    }

    public function setLabjournal(?string $labjournal): self
    {
        $this->labjournal = $labjournal;
        return $this;
    }
}
