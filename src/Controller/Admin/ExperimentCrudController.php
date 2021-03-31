<?php

namespace App\Controller\Admin;

use App\Entity\Experiment;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ExperimentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Experiment::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addPanel("General information"),
            IdField::new('id')->hideOnForm(),
            TextField::new('name'),
            AssociationField::new('owner'),
            AssociationField::new("experimentType"),

            FormField::addPanel("Experimental relations"),
            AssociationField::new("cells"),
            AssociationField::new("proteinTargets"),
            AssociationField::new("chemicals"),
            TextEditorField::new("lysing", "Lysis conditions")
                ->hideOnIndex(),
            TextEditorField::new("seeding", "Seeding conditions")
                ->hideOnIndex(),
        ];
    }
}
