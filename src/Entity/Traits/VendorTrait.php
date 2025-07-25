<?php
declare(strict_types=1);

namespace App\Entity\Traits;

use App\Entity\DoctrineEntity\Vendor;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\QueryBuilder;
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

    /**
     * @return array{
     *     0: FormField,
     *     1: AssociationField,
     *     2: TextField,
     * }
     */
    public static function crudFields(): array
    {
        return [
            FormField::addPanel("Vendor"),
            AssociationField::new("vendor", "Vendor")
                ->setRequired(false)
                ->hideOnIndex()
                ->autocomplete()
                ->setQueryBuilder(fn(QueryBuilder $builder) => $builder->orderBy("entity.isPreferred", "DESC")->orderBy("entity.name", "ASC")),
            TextField::new("vendorPN", "Vendor PN")
                ->hideOnIndex()
                ->setHelp("Product number of the vendor."),
        ];
    }
}
