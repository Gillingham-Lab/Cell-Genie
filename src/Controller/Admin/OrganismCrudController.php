<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\DoctrineEntity\Vocabulary\Organism;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

/**
 * @extends AbstractCrudController<Organism>
 */
class OrganismCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Organism::class;
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
