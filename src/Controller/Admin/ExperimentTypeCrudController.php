<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ExperimentType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ExperimentTypeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ExperimentType::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addPanel("Meta data"),
            IdField::new('id')->hideOnForm(),
            TextField::new('name')
                ->setRequired(true),
            AssociationField::new("parent")
                ->setHelp("Setting a parent lets you use experimental detail of the parent."),

            FormField::addPanel("Experimental detail")
                ->hideOnIndex(),
            AssociationField::new("wellplate", "Recommended wellplate"),
            TextEditorField::new("description", "Experimental procedure")
                ->hideOnIndex(),
            TextEditorField::new("lysing", "Lysis conditions")
                ->hideOnIndex(),
            TextEditorField::new("seeding", "Seeding conditions")
                ->hideOnIndex(),
        ];
    }
}
