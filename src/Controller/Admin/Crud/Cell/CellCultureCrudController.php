<?php
declare(strict_types=1);

namespace App\Controller\Admin\Crud\Cell;

use App\Entity\DoctrineEntity\Cell\CellCulture;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<CellCulture>
 */
class CellCultureCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CellCulture::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new("id")
                ->onlyOnIndex(),
            TextField::new("number")
                ->setLabel("Number")
                ->setHelp("Give the cell culture a number to easily identify and cross-reference it with the lab journal, eg FLC001 (First name, last name, Cell)"),
            AssociationField::new("owner")
                ->setLabel("Scientist")
                ->setHelp("Set the scientist who is responsible for thawing this cell line."),
            AssociationField::new("aliquot")
                ->setLabel("Aliquot")
                ->setHelp("Reference the aliquot you have used to thaw this cell line. Leave empty if the culture comes from splitting."),
            AssociationField::new("parentCellCulture")
                ->setLabel("Parent cell culture")
                ->setHelp("Reference the parent cell culture in case ths culture comes from splitting."),

            DateField::new("unfrozenOn")
                ->setLabel("Created")
                ->setHelp("On which day was this cell culture created?"),
            DateField::new("trashedOn")
                ->setLabel("Trashed")
                ->setHelp("On which day was this cell culture trashed? Trashed cell lines (with a trash date before today) do not appear in the cell culture overview"),

            TextField::new("incubator")
                ->setLabel("Incubator name"),

            TextField::new("flask")
                ->setLabel("Flask type (T25, T75, ...)"),
        ];
    }
}
