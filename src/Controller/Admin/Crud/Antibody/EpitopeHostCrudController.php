<?php
declare(strict_types=1);

namespace App\Controller\Admin\Crud\Antibody;

use App\Entity\EpitopeHost;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EpitopeHostCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return EpitopeHost::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('shortName', label: "Short name of the epitope"),
        ];
    }
}
