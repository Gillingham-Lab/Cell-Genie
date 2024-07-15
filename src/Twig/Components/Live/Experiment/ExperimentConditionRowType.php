<?php
declare(strict_types=1);

namespace App\Twig\Components\Live\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDatum;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDesignField;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunCondition;
use App\Entity\DoctrineEntity\Form\FormRow;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Form\LinkedEntityType;
use App\Form\ScientificNumberType;
use App\Genie\Enums\DatumEnum;
use App\Genie\Enums\FormRowTypeEnum;
use App\Service\Experiment\ExperimentalDataFormRowService;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;

class ExperimentConditionRowType extends AbstractType
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private readonly ExperimentalDataFormRowService $formRowService,
    ) {

    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "fields" => null,
            "data_class" => ExperimentalRunCondition::class,
        ]);

        $resolver->setAllowedTypes("fields", Collection::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Add constant fields
        $builder
            ->add("name", TextType::class, [
                "label" => "Condition Name"
            ])
            ->add("control", CheckboxType::class, [
                "label" => "Control condition",
                "empty_data" => null,
                "required" => false,
            ])
        ;

        $this->formRowService->createBuilder($builder, "data", ... $options["fields"]);
    }
}