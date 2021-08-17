<?php
declare(strict_types=1);

namespace App\Entity\Trait;

use App\Entity\Vendor;
use Doctrine\ORM\Mapping as ORM;

trait VendorTrait
{
    /**
     * @ORM\ManyToOne(targetEntity=Vendor::class, fetch="EAGER")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @ORM\OrderBy({"isPreferred" = "DESC"})
     */
    private ?Vendor $vendor = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $vendorPN = null;

    public function getVendor(): ?Vendor
    {
        return $this->vendor;
    }

    public function setVendor(?Vendor $vendor): self
    {
        $this->vendor = $vendor;

        return $this;
    }

    public function getVendorPn(): ?string
    {
        return $this->vendorPN;
    }

    public function setVendorId(?string $vendorPN): self
    {
        $this->vendorPN = $vendorPN;

        return $this;
    }
}