<?php
declare(strict_types=1);

namespace App\Entity\Traits;

use App\Entity\Vendor;
use Doctrine\ORM\Mapping as ORM;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

trait VendorTrait
{
    #[ORM\ManyToOne(targetEntity: Vendor::class, fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    #[ORM\OrderBy(["isPreferred" => "DESC"])]
    private ?Vendor $vendor = null;
    
    #[ORM\Column(type: "string", length: 255, nullable: true)]
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

    public function setVendorPn(?string $vendorPN): self
    {
        $this->vendorPN = $vendorPN;

        return $this;
    }

    public static function crudFields(): array
    {
        return [
            FormField::addPanel("Vendor"),
            AssociationField::new("vendor", "Vendor")
                ->hideOnIndex(),
            TextField::new("vendorPN", "Vendor PN")
                ->hideOnIndex()
                ->setHelp("Product number of the vendor."),
        ];
    }
}