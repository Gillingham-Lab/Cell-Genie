<?php declare(strict_types=1);

namespace App\Controller\Admin\Crud\Cell;

use App\Entity\DoctrineEntity\Cell\CellCultureSplittingEvent;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<CellCultureSplittingEvent>
 */
class CellCultureSplittingEventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CellCultureSplittingEvent::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addPanel("General event information"),
            IdField::new("id")
                ->onlyOnIndex(),
            AssociationField::new("cellCulture")
                ->setLabel("Cell Culture")
                ->setHelp("The cell culture where this event happened."),
            TextField::new("shortName"),
            AssociationField::new("owner")
                ->setLabel("Scientist")
                ->setHelp("Set the scientist responsible for this event."),
            DateField::new("date")
                ->setFormat("Y M d"),
            TextareaField::new("description")
                ->hideOnIndex(),

            FormField::addPanel("Splitting details"),
            TextField::new("splitting")
                ->setLabel("Splitting")
                ->setHelp("A short text describing how you split the cells (cell amount, or % of cells ...)"),
            TextField::new("newFlask")
                ->setLabel("New Flask")
                ->setHelp("What flask did you change into?"),
        ];
    }
}
