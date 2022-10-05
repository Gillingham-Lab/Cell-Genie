<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Experiment;
use App\Form\ExperimentalConditionType;
use App\Form\ExperimentalMeasurementType;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ExperimentCrudController extends ExtendedAbstractCrudController
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
            AssociationField::new("experimentType")
                ->hideOnIndex()
                ->setHelp("Set a overarching experiment type to organize the experiments."),
            TextField::new('name')
                ->setHelp("Name of the experiment"),
            AssociationField::new('owner')
                ->setHelp("Who owns this experiment? Only owner can change an experiment outside of the admin dashboard."),

            FormField::addPanel("Experimental details"),
            CollectionField::new("conditions", "Conditions")
                ->setEntryType(ExperimentalConditionType::class)
                ->setEntryIsComplex(true),
            CollectionField::new("measurements", "Measurements")
                ->setEntryType(ExperimentalMeasurementType::class)
                ->setEntryIsComplex(true),
        ];
    }
}
