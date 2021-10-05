<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\AntibodyDilution;
use App\Entity\ExperimentType;
use App\Form\LotType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
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
                ->setHelp("Setting a parent helps to organise experiments hierarchically."),
            TextEditorField::new("description", "Description")
                ->hideOnIndex()
                ->setHelp("Describe briefly the experiment type. What is it used for?"),
            AssociationField::new("createdBy")
                ->setHelp("Creator of the experiment type."),
        ];
    }
}
