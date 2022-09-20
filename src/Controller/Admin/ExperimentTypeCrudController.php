<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\AntibodyDilution;
use App\Entity\ExperimentType;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ExperimentTypeCrudController extends ExtendedAbstractCrudController
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
                ->setHelp("Setting a parent helps to organise experiments hierarchically."),
            TextareaField::new("description", "Description")
                ->hideOnIndex()
                ->setHelp("Describe briefly the experiment type. What is it used for?"),
            AssociationField::new("createdBy")
                ->setHelp("Creator of the experiment type."),
        ];
    }
}
