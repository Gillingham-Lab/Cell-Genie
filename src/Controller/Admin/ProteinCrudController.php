<?php

namespace App\Controller\Admin;

use App\Entity\Protein;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class ProteinCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Protein::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('shortName', label: "Gene name (like MGMT)"),
            TextField::new('longName', label: "Long name"),
            UrlField::new("proteinAtlasUri", label: "URL to the protein atlas"),
        ];
    }
}
