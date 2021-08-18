<?php

namespace App\Controller\Admin;

use App\Entity\AntibodyHost;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AntibodyHostCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AntibodyHost::class;
    }

        public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('name', label: "Name of the host organism"),
        ];
    }
}
