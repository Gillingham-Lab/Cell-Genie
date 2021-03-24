<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Morphology;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class MorphologyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Morphology::class;
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
