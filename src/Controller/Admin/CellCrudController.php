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
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
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
            AssociationField::new("morphology")
                ->setRequired(true),
            AssociationField::new("organism")
                ->setRequired(true),
            AssociationField::new("tissue")
                ->setRequired(true),
            TextField::new("cultureType")
                ->setRequired(true),
            BooleanField::new("isCancer"),
            BooleanField::new("isEngineered"),

            FormField::addPanel("Origins"),
            TextareaField::new("origin"),
            AssociationField::new("vendor", "Vendor"),
            TextField::new("vendorId", "Vendor PN")
                ->setHelp("Product number of the vendor."),
            DateField::new("acquiredOn")
                ->setFormat("MEDIUM"),
            NumberField::new("price"),
            AssociationField::new("boughtBy"),

            FormField::addPanel("Call management conditions"),
            TextEditorField::new("medium", label: "Recommended cell medium")
                ->hideOnIndex(),
            TextEditorField::new("trypsin", label: "Required trypsin")
                ->hideOnIndex(),
            TextEditorField::new("splitting", label: "Recommended splitting protocol")
                ->hideOnIndex(),
            TextEditorField::new("freezing", label: "Recommended freezing conditions")
                ->hideOnIndex(),
            TextEditorField::new("thawing", label: "Recommended thawing conditions")
                ->hideOnIndex(),
            TextEditorField::new("cultureConditions", label: "Growth conditions for incubator")
                ->hideOnIndex(),

            FormField::addPanel("Basic experimental conditions"),
            TextEditorField::new("seeding", label: "Detailed hints on cell seeding (cell amount, medium volume, time until confluency, wellplate)")
                ->setHelp("A good seeding recommendation is for a 12-well plate.")
                ->hideOnIndex(),
            IntegerField::new("countOnConfluence", label: "Cell count on confluence")
                ->setHelp("Try to keep the well-plate format consistent between seeding and cell seeding conditions. If you give multiple recommendations, highlight the one used for this field.")
                ->hideOnIndex(),
            TextEditorField::new("lysing", label: "Recommended cell lysis")
                ->hideOnIndex(),
        ];
    }
}
