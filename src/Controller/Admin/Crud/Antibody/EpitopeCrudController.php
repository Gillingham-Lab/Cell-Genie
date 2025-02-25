<?php
declare(strict_types=1);


namespace App\Controller\Admin\Crud\Antibody;

use App\Entity\DoctrineEntity\Epitope;
use App\Entity\EpitopeProtein;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EpitopeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Epitope::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('shortName', label: "Short name of the epitope"),
            TextareaField::new("description", label: "Description")
                ->setHelp("Additional information on the epitope, if known.")
                ->hideOnIndex(),
            AssociationField::new("substances", label: "Substance target")
                ->setHelp("Register proteins that have this epitope.")
                ->hideOnIndex(),
        ];
    }
}
