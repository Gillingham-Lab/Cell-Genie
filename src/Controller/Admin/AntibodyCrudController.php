<?php

namespace App\Controller\Admin;

use App\Entity\Antibody;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AntibodyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Antibody::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addPanel("General properties"),
            IdField::new("id")->hideOnForm(),
            TextField::new("number", label: "Number")
                ->setHelp("A short number used to identify the antibody in our system. Different vendor should use different number!"),
            TextField::new("shortName")
                ->setHelp("A combination of protein target and antibody source or detection would be helpful, such as MGMT (goat), or Goat (VIS 700)"),
            TextField::new("longName"),
            AssociationField::new("vendor"),
            TextField::new("vendorPN", label: "Vendor product number"),

            FormField::addPanel("Experimental"),
            TextField::new("detection", label: "Way of detection")
                ->setHelp("Leave empty if there is no reporter."),
            AssociationField::new("proteinTarget", label: "Protein target")
                ->setHelp("Leave empty if its a secondary antibody."),
            AssociationField::new("secondaryAntibody", label: "Secondary antibody")
                ->setHelp("Add secondary antibodies known to work with this antibody."),
            AssociationField::new("antibodies", label: "Targeted antibodies")
                ->setHelp("List here other antibodies this antibody can work against.")
                ->setFormTypeOption("by_reference", false),
        ];
    }
}
