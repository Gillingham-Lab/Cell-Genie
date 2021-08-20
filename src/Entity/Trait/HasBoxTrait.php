<?php
declare(strict_types=1);

namespace App\Entity\Trait;

use App\Entity\Box;
use Doctrine\ORM\Mapping as ORM;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

trait HasBoxTrait
{
    /**
     * @ORM\ManyToOne(targetEntity=Box::class)
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private ?Box $box = null;

    public function getBox(): ?Box
    {
        return $this->box;
    }

    public function setBox(?Box $box): self
    {
        $this->box = $box;

        return $this;
    }

    public static function crudField(): array
    {
        return [
            AssociationField::new("box")
        ];
    }
}