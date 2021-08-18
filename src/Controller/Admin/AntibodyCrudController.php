<?php

namespace App\Controller\Admin;

use App\Entity\Antibody;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
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
                ->setHelp("A short number used to identify the antibody in our system."),
            TextField::new("shortName")
                ->setHelp("A combination of protein target and antibody source or detection would be helpful, such as MGMT (goat), or Goat (VIS 700)"),
            TextField::new("longName", label: "Long name"),
            TextField::new("rrid", label: "#RRID")
                ->setHelp("RRID is a research resource identification and is similar to a doi, except that everything can have a rrid, including antibodies."),
            IntegerField::new("storageTemperature", label: "Storage temperature (°C)")
                ->setHelp("Note down a storage temperature between -200 and 25 °C. Commonly, -20 °C is used."),
            AssociationField::new("hostOrganism", label: "Host Organism")
                ->setHelp("Host organism for this antibody. Important to automatically determine secondary antibodies."),
            TextField::new("clonality", label: "Clonality")
                ->setHelp("Usually, this is either 'monoclonal' or 'polyclonal'."),

            FormField::addPanel("Vendor"),
            AssociationField::new("vendor"),
            TextField::new("vendorPN", label: "Vendor product number"),

            FormField::addPanel("Validation"),
            BooleanField::new("validatedInternally")
                ->setHelp("Set to true if we tested it to be working internally."),
            BooleanField::new("validatedExternally")
                ->setHelp("Set to true if there is a (peer-reviewed) publication using it."),
            TextField::new("externalReference")
                ->hideOnIndex()
                ->setHelp("Give a reference (doi preferred) where this antibody has been used before."),

            FormField::addPanel("Experimental"),
            TextEditorField::new("dilution", label: "Dilution suggestions")
                ->setHelp("Oftentimes, vendor propose antibody dilutions for specific applications."),
            TextField::new("detection", label: "Way of detection")
                ->setHelp("Leave empty if there is no reporter.")
                ->hideOnIndex(),
            AssociationField::new("proteinTarget", label: "Protein target")
                ->setHelp("Leave empty if its a secondary antibody.")
                ->hideOnIndex(),
            AssociationField::new("hostTarget", label: "Host Target")
                ->setHelp("Add which host this antibody targets. Leave empty for primary antibodies.")
                ->hideOnIndex(),
        ];
    }
}
