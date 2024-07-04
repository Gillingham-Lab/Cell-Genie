<?php
declare(strict_types=1);

namespace App\Controller\Admin\Crud;

use App\Entity\Lot;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use SebastianBergmann\CodeCoverage\Report\Text;

class LotCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Lot::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab("General"),
            IdField::new('id')
                ->hideOnForm(),
            TextField::new("number")
                ->setHelp("Internal number to keep track of individual lots. For synthesised compounds, this can be labjournal number + batch."),
            TextField::new("lotNumber")
                ->setHelp("Vendor lot number to identify the lot. For synthesised compounds, this should be labjournal number + batch."),
            DateField::new("boughtOn")
                ->setFormat("Y M d")
                ->setHelp("Date this got bought on."),
            AssociationField::new("boughtBy")
                ->setLabel("Bought by"),
            DateField::new("openedOn")
                ->setFormat("Y M d")
                ->setHelp("Date this got opened on (can help to keep track how decomposed something might be)"),
            TextareaField::new("comment")
                ->setHelp("Some additional information that you might think is important."),

            FormField::addTab("Container"),
            AssociationField::new("box"),
            TextField::new("amount")
                ->setLabel("Amount")
                ->setHelp("Write down the (total) size of the bottle, including a unit."),
            TextField::new("purity")
                ->setLabel("Concentration")
                ->setHelp("Write down the concentration of the compound in the bottle, including a unit."),
            IntegerField::new("numberOfAliquotes"),
            TextField::new("aliquoteSize")
                ->setLabel("Aliquot size")
                ->setHelp("The size of a individual aliquot."),

            FormField::addTab("Vendor"),
            AssociationField::new("vendor")
                ->setLabel("Vendor")
                ->setHelp("Keep empty if the substance has the vendor information (like in the case of antibodies)"),
            TextField::new("vendorPN")
                ->setLabel("Product number")
        ];
    }
}