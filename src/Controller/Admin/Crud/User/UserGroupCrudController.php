<?php
declare(strict_types=1);

namespace App\Controller\Admin\Crud\User;

use App\Entity\DoctrineEntity\User\UserGroup;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<UserGroup>
 */
class UserGroupCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserGroup::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new("id")
                ->hideOnForm(),
            TextField::new("shortName"),
        ];
    }
}
