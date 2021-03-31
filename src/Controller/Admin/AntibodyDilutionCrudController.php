<?php

namespace App\Controller\Admin;

use App\Entity\AntibodyDilution;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class AntibodyDilutionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AntibodyDilution::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
