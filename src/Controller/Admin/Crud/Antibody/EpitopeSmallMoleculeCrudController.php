<?php
declare(strict_types=1);

namespace App\Controller\Admin\Crud\Antibody;

use App\Entity\EpitopeSmallMolecule;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EpitopeSmallMoleculeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return EpitopeSmallMolecule::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('shortName', label: "Short name of the epitope"),
            AssociationField::new("chemicals", label: "Chemical target")
                ->setHelp("Register chemicals that have this epitope.")
                ->hideOnIndex(),
        ];
    }
}
