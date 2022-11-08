<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Rack;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class RackCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Rack::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('ulid')
                ->hideOnForm(),
            TextField::new('name'),
            AssociationField::new("parent"),
            AssociationField::new("children")->onlyOnIndex(),
            AssociationField::new("boxes")->onlyOnIndex(),
        ];
    }
}
