<?php
declare(strict_types=1);


namespace App\Controller\Admin\Crud\Antibody;

use App\Entity\EpitopeProtein;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EpitopeProteinCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return EpitopeProtein::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('shortName', label: "Short name of the epitope"),
            AssociationField::new("proteins", label: "Protein target")
                ->setHelp("Register proteins that have this epitope.")
                ->hideOnIndex(),
        ];
    }
}
