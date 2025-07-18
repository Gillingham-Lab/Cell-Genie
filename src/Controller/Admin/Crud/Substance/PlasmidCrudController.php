<?php
declare(strict_types=1);

namespace App\Controller\Admin\Crud\Substance;

use App\Controller\Admin\Crud\LotCrudController;
use App\Controller\Admin\Traits\VocabularyTrait;
use App\Entity\DoctrineEntity\Substance\Plasmid;
use App\Repository\Vocabulary\VocabularyRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Plasmid>
 */
class PlasmidCrudController extends AbstractCrudController
{
    use VocabularyTrait;

    public function __construct(
        private readonly VocabularyRepository $vocabularyRepository,
    ) {}

    public static function getEntityFqcn(): string
    {
        return Plasmid::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->setPermission(Action::DELETE, "ROLE_ADMIN");
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab("General information"),
            IdField::new('ulid')->hideOnForm(),
            TextField::new("number", label: "A short number"),
            TextField::new('shortName', label: "Plasmid name (like MGMT)"),
            TextField::new('longName', label: "Long name"),

            AssociationField::new("createdBy"),
            TextField::new("labjournal", label: "Lab journal entry"),
            TextareaField::new("comment")->hideOnIndex(),

            FormField::addTab("Lot entries"),
            CollectionField::new("lots", "Lot entries")
                ->useEntryCrudForm(LotCrudController::class)
                ->hideOnIndex()
                ->allowDelete(true),
        ];
    }
}
