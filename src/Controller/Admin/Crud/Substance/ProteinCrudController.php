<?php
declare(strict_types=1);

namespace App\Controller\Admin\Crud\Substance;

use App\Controller\Admin\Crud\LotCrudController;
use App\Controller\Admin\Traits\VocabularyTrait;
use App\Entity\DoctrineEntity\Substance\Protein;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

/**
 * @extends AbstractCrudController<Protein>
 */
class ProteinCrudController extends AbstractCrudController
{
    use VocabularyTrait;

    public function __construct(
        private readonly VocabularyRepository $vocabularyRepository,
    ) {}

    public static function getEntityFqcn(): string
    {
        return Protein::class;
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
            TextField::new('shortName', label: "Gene name (like MGMT)"),
            TextField::new('longName', label: "Long name"),
            UrlField::new("proteinAtlasUri", label: "URL to the protein atlas"),

            FormField::addPanel("Structural information"),
            $this->textFieldOrChoices("proteinType")
                ->setHelp("Specify the type of the protein (wildtype, point mutant, isoform ...). Additional context is given by the parent."),

            TextareaField::new('fastaSequence', label: "Sequence")
                ->hideOnIndex()
                ->setHelp("The one letter amino acid code, sequence only (no fasta header)"),
            TextField::new('mutation', label: "Point mutations")
                ->setHelp("In the form of G12C, for example. Make sure the sequence is correct."),

            FormField::addPanel("Relationships"),
            AssociationField::new("children", label: "Children proteins")
                ->onlyOnIndex(),
            AssociationField::new("parents", label: "Parent proteins")
                ->setHelp("Multiple parents are possible if the protein is a fusion, for example"),

            FormField::addTab("Experimental details"),
            AssociationField::new("epitopes", label: "Epitopes of this protein.")
                ->setFormTypeOption("by_reference", false),

            FormField::addTab("Lot entries"),
            CollectionField::new("lots", "Lot entries")
                ->useEntryCrudForm(LotCrudController::class)
                ->hideOnIndex()
                ->allowDelete(true),
        ];
    }
}
