<?php
declare(strict_types=1);

namespace App\Controller\Admin\Crud\Substance;

use App\Controller\Admin\Crud\LotCrudController;
use App\Controller\Admin\ExtendedAbstractCrudController;
use App\Controller\Admin\VocabularyTrait;
use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Form\AdminCrud\DocumentationType;
use App\Genie\Enums\AntibodyType;
use App\Repository\VocabularyRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Security\Core\Security;

class AntibodyCrudController extends ExtendedAbstractCrudController
{
    use VocabularyTrait;

    public function __construct(
        private Security $security,
        private VocabularyRepository $vocabularyRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Antibody::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->setPermission(Action::DELETE, "ROLE_ADMIN");
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab("General"),
            IdField::new("ulid")->hideOnForm(),
            TextField::new("number", label: "Number")
                ->setHelp("A short number used to identify the antibody in our system."),
            TextField::new("shortName")
                ->setHelp("A combination of protein target and antibody source or detection would be helpful, such as MGMT (goat), or Goat (VIS 700)"),
            TextField::new("longName", label: "Long name"),
            ... Antibody::rridCrudFields(),
            ChoiceField::new("type", label: "Antibody type")
                ->onlyOnIndex()
                ->setChoices(function () {
                    $choices = array_map(static fn (?AntibodyType $type) => [$type->value => $type->name], AntibodyType::cases());
                    return array_merge(...$choices);
                })
                ->setRequired(true)
                ->setFormType(EnumType::class)
                ->setFormTypeOption('class', AntibodyType::class)
                ->setFormTypeOption('choice_label', function (AntibodyType $enum) {
                    return $enum->value;
                }),

            ChoiceField::new("type", label: "Antibody type")
                ->onlyOnForms()
                ->setChoices(function () {
                    $choices = array_map(static fn (?AntibodyType $type) => [$type->value => $type], AntibodyType::cases());
                    return array_merge(...$choices);
                })
                ->setRequired(true)
                ->setFormType(EnumType::class)
                ->setFormTypeOption('class', AntibodyType::class)
                ->setFormTypeOption('choice_label', function (AntibodyType $enum) {
                    return $enum->value;
                }),
            IntegerField::new("storageTemperature", label: "Storage temperature (°C)")
                ->setHelp("Note down a storage temperature between -200 and 25 °C. Commonly, -20 °C is used."),

            FormField::addPanel("Properties"),
            AssociationField::new("epitopes", label: "Epitopes")
                ->setHelp("Epitopes this antibody has."),
            AssociationField::new("epitopeTargets", label: "Epitopes")
                ->setHelp("SEpitopes this antibody targets."),
            $this->textFieldOrChoices("clonality")
                ->setHelp("Usually, this is either 'monoclonal' or 'polyclonal'."),
            TextField::new("usage", label: "Purpose")
                ->setHelp("Highlight the purpose for this antibody (WB, IP, IH, ...). Highlight with 'Only' if the antibody is for a specific purpose."),

            FormField::addTab("Origin"),
            AssociationField::new("vendor")->hideOnIndex(),
            TextField::new("vendorPN", label: "Vendor product number")->hideOnIndex(),

            FormField::addTab("Validation"),
            BooleanField::new("validatedInternally")
                ->setHelp("Set to true if we tested it to be working internally."),
            BooleanField::new("validatedExternally")
                ->setHelp("Set to true if there is a (peer-reviewed) publication using it."),
            TextField::new("externalReference")
                ->hideOnIndex()
                ->setHelp("Give a reference (doi preferred) where this antibody has been used before."),

            FormField::addTab("Experimental"),
            TextEditorField::new("dilution", label: "Dilution suggestions")
                ->setHelp("Oftentimes, vendor propose antibody dilutions for specific applications.")
                ->hideOnIndex(),
            TextField::new("detection", label: "Way of detection")
                ->setHelp("Leave empty if there is no reporter.")
                ->hideOnIndex(),
            /*AssociationField::new("proteinTarget", label: "Protein target")
                ->setHelp("Leave empty if its a secondary antibody.")
                ->hideOnIndex(),*/
            /*AssociationField::new("hostTarget", label: "Host Target")
                ->setHelp("Add which host this antibody targets. Leave empty for primary antibodies.")
                ->hideOnIndex(),*/

            FormField::addTab("Lot entries"),
            CollectionField::new("lots", "Lot entries")
                ->useEntryCrudForm(LotCrudController::class)
                ->hideOnIndex()
                ->allowDelete(True),

            FormField::addTab("Documentation"),
            CollectionField::new("vendorDocumentation", "Attachments")
                ->setHelp("Add file attachments to provide complete documentation.")
                ->setEntryType(DocumentationType::class)
                ->setEntryIsComplex(true)
                ->hideOnIndex()
                ->allowDelete(True),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters

            ->add("validatedInternally")
            ->add("vendor")
        ;
    }
}
