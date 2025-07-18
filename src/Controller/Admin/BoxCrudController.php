<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\DoctrineEntity\Storage\Box;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Box>
 */
class BoxCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Box::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('ulid')
                ->hideOnForm(),
            TextField::new('name'),
            IntegerField::new("rows"),
            IntegerField::new("cols"),
            AssociationField::new("rack"),
        ];
    }
}
