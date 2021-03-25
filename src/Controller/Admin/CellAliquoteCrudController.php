<?php

namespace App\Controller\Admin;

use App\Entity\CellAliquote;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CellAliquoteCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CellAliquote::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addPanel("Aliquote information"),
            IdField::new('id')
                ->hideOnForm(),
            AssociationField::new("cell"),
            DateTimeField::new('aliquoted_on')
                ->setFormat("MEDIUM"),
            AssociationField::new('aliquoted_by'),
            AssociationField::new("box"),
            TextField::new("vialColor"),
            IntegerField::new("passage"),
            IntegerField::new("cellCount"),
            IntegerField::new("vials"),

            FormField::addPanel("Testing"),
            TextEditorField::new("mycoplasma")
                ->hideOnIndex(),
            TextEditorField::new("typing")
                ->hideOnIndex(),
            TextEditorField::new("history")
                ->hideOnIndex(),
        ];
    }
}
