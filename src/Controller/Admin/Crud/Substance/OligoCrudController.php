<?php
declare(strict_types=1);

namespace App\Controller\Admin\Crud\Substance;

use App\Controller\Admin\Crud\LotCrudController;
use App\Entity\DoctrineEntity\Substance\Oligo;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class OligoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Oligo::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab("General information"),
            IdField::new('ulid')->hideOnForm(),
            TextField::new('shortName'),
            TextField::new('longName'),
            TextareaField::new("comment")->hideOnIndex()
                ->setHelp("Annotate for what the oligo was used, or other details."),

            FormField::addTab("Structure"),
            IntegerField::new("sequenceLength", label: "Length")->onlyOnIndex()->setVirtual(true),
            TextareaField::new('sequence', label: "Sequence")
                ->setHelp("Add in the sequence of the oligo. Use the square bracket notation for modifications. * annotates a thiophosphate bond."),

            FormField::addTab("Lot entries"),
            CollectionField::new("lots", "Lot entries")
                ->useEntryCrudForm(LotCrudController::class)
                ->hideOnIndex()
                ->allowDelete(True),
        ];
    }
}
