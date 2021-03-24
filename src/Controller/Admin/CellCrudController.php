<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Cell;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class CellCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Cell::class;
    }

    /*public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }*/
}
