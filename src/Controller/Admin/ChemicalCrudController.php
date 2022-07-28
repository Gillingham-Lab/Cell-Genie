<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Chemical;
use App\Entity\Traits\VendorTrait;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
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
            IdField::new('ulid')->hideOnForm(),
            TextField::new('shortName'),
            TextField::new('longName'),
            TextField::new("casNumber"),
            TextField::new('smiles'),
            UrlField::new('labjournal'),
            ...VendorTrait::crudFields(),

            FormField::addPanel("Properties"),
            NumberField::new("molecularMass")
                ->setCustomOption(NumberField::OPTION_NUM_DECIMALS, 3),
            NumberField::new("density")
                ->hideOnIndex(),
        ];
    }
}
