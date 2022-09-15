<?php
declare(strict_types=1);

namespace App\Controller\Admin\Crud\Substance;

use App\Controller\Admin\Crud\LotCrudController;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\Traits\VendorTrait;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class ChemicalCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Chemical::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab("General"),
            IdField::new('ulid')->hideOnForm(),
            TextField::new('shortName'),
            TextField::new('longName'),
            TextField::new("casNumber"),
            TextField::new("iupacName")
                ->setHelp("Official IUPAC name (or as generated by ChemDraw)"),
            UrlField::new('labjournal'),
            TextField::new('smiles')
                ->setHelp("The SMILES is used to show a structure for the chemical. Use Ctrl+Alt+C in ChemDraw to copy the SMILES."),

            FormField::addTab("Chemical properties"),
            NumberField::new("molecularMass")
                ->setCustomOption(NumberField::OPTION_NUM_DECIMALS, 3),
            NumberField::new("density")
                ->setHelp("Mostly used for buffer recipes to calculate a volume. Only meaningful for liquids.")
                ->hideOnIndex(),

            FormField::addTab("Lot entries"),
            CollectionField::new("lots", "Lot entries")
                ->useEntryCrudForm(LotCrudController::class)
                ->hideOnIndex()
                ->allowDelete(True),

            FormField::addTab("Vendor"),
            ...VendorTrait::crudFields(),
        ];
    }
}
