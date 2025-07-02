<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\DoctrineEntity\Vendor;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

/**
 * @extends AbstractCrudController<Vendor>
 */
class VendorCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Vendor::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('name'),
            UrlField::new('catalogUrl')
                ->setHelp(<<<TXT
                Use {pn} to annotate where the product number should be inserted. If not given, it will 
                always be attached to the end. If there is no easy catalog access via product number, 
                add # to the end of the url.    
                TXT),
            BooleanField::new("isPreferred"),
            BooleanField::new("hasDiscount"),
            BooleanField::new("hasFreeShipping"),
            TextEditorField::new("comment"),
        ];
    }

}
