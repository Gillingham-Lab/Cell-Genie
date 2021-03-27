<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Chemical;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class ChemicalCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Chemical::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('shortName'),
            TextField::new('longName'),
            TextField::new('smiles'),
            UrlField::new('labjournal'),
        ];
    }
}
