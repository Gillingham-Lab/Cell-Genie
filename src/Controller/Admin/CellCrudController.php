<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\Traits\HasAttachmentsTrait;
use App\Entity\Traits\HasRRID;
use App\Entity\Traits\VendorTrait;
use App\Form\CellularProteinType;
use App\Repository\VocabularyRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\F;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CellCrudController extends ExtendedAbstractCrudController
{
    use VocabularyTrait;

    public function __construct(
        private VocabularyRepository $vocabularyRepository,
    ) {

    }

    public static function getEntityFqcn(): string
    {
        return Cell::class;
    }

    public function configureFields(string $pageName): iterable
    {

        return [
            FormField::addTab("Cell properties"),
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('cellNumber'),
            TextField::new('name'),
            ... HasRRID::rridCrudFields(),
            TextField::new('cellosaurusId')
                ->setLabel("Cellosaurus ID")
                ->setRequired(false)
                ->hideOnIndex(),
            TextField::new('age')
                ->hideOnIndex()
                ->setRequired(false),
            $this->textFieldOrChoices("sex")
                ->hideOnIndex()
                ->setRequired(false),
            $this->textFieldOrChoices("ethnicity")
                ->setRequired(false)
                ->hideOnIndex()
                ->setHelp("According to cellosaurus genome origin"),
            TextField::new('disease')
                ->hideOnIndex()
                ->setRequired(false),
            AssociationField::new("morphology")
                ->setRequired(true)
                ->hideOnIndex(),
            AssociationField::new("organism")
                ->setRequired(true),
            AssociationField::new("tissue")
                ->setRequired(false),
            $this->textFieldOrChoices("cultureType")
                ->setRequired(true),
            BooleanField::new("isCancer"),

            FormField::addTab("Origins"),
            TextEditorField::new("origin")
                ->hideOnIndex(),
            ... VendorTrait::crudFields(),
            DateField::new("acquiredOn")
                ->hideOnIndex()
                ->setFormat("MEDIUM"),
            NumberField::new("price")
                ->hideOnIndex(),
            AssociationField::new("boughtBy")
                ->hideOnIndex(),
            TextEditorField::new("originComment", label: "Comment")
                ->hideOnIndex()
                ->setHelp("Add some additional details on how we acquired the cells."),

            FormField::addTab("Engineering"),
            BooleanField::new("isEngineered"),
            AssociationField::new("engineer")->hideOnIndex(),
            AssociationField::new("parent")
                ->hideOnIndex()
                ->setQueryBuilder(fn (QueryBuilder $builder) => $builder->orderBy("entity.cellNumber", "ASC")),
            TextEditorField::new("engineeringDescription")
                ->setHelp("Details on what was modified compared to the parent cell. Please reference lab journal or publications for more details, too.")
                ->hideOnIndex(),
            TextField::new("engineeringPlasmid")
                ->setRequired(false)
                ->hideOnIndex()
                ->setLabel("Plasmid reference")
                ->setHelp("A reference to the plasmid or a short description thereof"),

            FormField::addTab("Cell management conditions"),
            TextEditorField::new("medium", label: "Recommended cell medium")
                ->hideOnIndex(),
            TextEditorField::new("trypsin", label: "Required trypsin")
                ->hideOnIndex(),
            TextEditorField::new("splitting", label: "Recommended splitting protocol")
                ->hideOnIndex(),
            TextEditorField::new("freezing", label: "Recommended freezing conditions")
                ->hideOnIndex(),
            TextEditorField::new("thawing", label: "Recommended thawing conditions")
                ->hideOnIndex(),
            TextEditorField::new("cultureConditions", label: "Growth conditions for incubator")
                ->hideOnIndex(),

            FormField::addTab("Basic experimental conditions"),
            TextEditorField::new("seeding", label: "Detailed hints on cell seeding (cell amount, medium volume, time until confluency, wellplate)")
                ->setHelp("A good seeding recommendation is for a 12-well plate.")
                ->hideOnIndex(),
            IntegerField::new("countOnConfluence", label: "Cell count on confluence")
                ->setHelp("Try to keep the well-plate format consistent between seeding and cell seeding conditions. If you give multiple recommendations, highlight the one used for this field.")
                ->hideOnIndex(),
            TextEditorField::new("lysing", label: "Recommended cell lysis")
                ->hideOnIndex(),

            FormField::addTab("Expression"),
            CollectionField::new("cellProteins", "Cellular Proteins")
                ->hideOnIndex()
                ->setEntryType(CellularProteinType::class)
                ->setEntryIsComplex(true),

            FormField::addTab("Attachments"),
            ... HasAttachmentsTrait::attachmentCrudFields(),
        ];
    }
}
