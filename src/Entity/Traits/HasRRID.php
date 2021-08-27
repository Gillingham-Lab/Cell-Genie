<?php
declare(strict_types=1);

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * Provides a RRID field for entities.
 */
trait HasRRID
{
    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $rrid = null;

    public static function rridCrudFields(): array
    {
        return [
            TextField::new("rrid", label: "#RRID")
                ->setHelp("RRID is a research resource identification and is similar to a doi, except that everything can have a rrid, including antibodies.")
        ];
    }

    public function getRrid(): ?string
    {
        return $this->rrid;
    }

    public function setRrid(?string $rrid): self
    {
        $this->rrid = $rrid;

        return $this;
    }
}