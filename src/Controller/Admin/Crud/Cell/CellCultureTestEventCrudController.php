<?php declare(strict_types=1);

namespace App\Controller\Admin\Crud\Cell;

use App\Entity\DoctrineEntity\Cell\CellCultureTestEvent;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<CellCultureTestEvent>
 */
class CellCultureTestEventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CellCultureTestEvent::class;
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
                ->setFormat("Y-m-d"),
            TextareaField::new("description")
                ->hideOnIndex(),

            FormField::addPanel("Test details"),
            ChoiceField::new("result")
                ->setTranslatableChoices(array_combine(CellCultureTestEvent::RESULTS, CellCultureTestEvent::RESULTS)),
            TextField::new("testType")
                ->hideOnIndex(),
            NumberField::new("supernatantAmount")
                ->hideOnIndex(),
        ];
    }
}
