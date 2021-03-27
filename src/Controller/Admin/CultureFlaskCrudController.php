<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\CultureFlask;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CultureFlaskCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CultureFlask::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('name'),
            IntegerField::new("rows"),
            IntegerField::new("cols"),
            TextareaField::new('comment'),
            AssociationField::new("vendor", "Vendor"),
            TextField::new("vendorId", "Vendor PN")
        ];
    }
}
