<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Cell;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Configurator\CurrencyConfigurator;
use EasyCorp\Bundle\EasyAdminBundle\Field\CurrencyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\F;

class CellCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Cell::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addPanel("Cell properties"),
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('name'),
            TextField::new('age'),
            AssociationField::new("parent"),
            AssociationField::new("morphology"),
            AssociationField::new("organism"),
            AssociationField::new("tissue"),
            BooleanField::new("isCancer"),
            BooleanField::new("isEngineered"),
            FormField::addPanel("Origins"),
            TextareaField::new("origin"),
            TextField::new("vendor", "Vendor"),
            TextField::new("vendorId", "Vendor PN")
                ->setHelp("Product number of the vendor."),
            DateField::new("acquiredOn")
                ->setFormat("MEDIUM"),
            NumberField::new("price"),
            AssociationField::new("boughtBy"),
        ];
    }
}
