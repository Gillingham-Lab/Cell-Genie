<?php
declare(strict_types=1);

namespace App\Form\Form;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDesignField;
use App\Form\BasicType\ExpressionType;
use App\Form\BasicType\FormGroupType;
use App\Service\Experiment\ExperimentalModelService;
use App\Validator\Constraint\ValidExpression;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @template TData
 * @extends AbstractType<TData>
 */
class ExpressionTypeConfigurationType extends AbstractType
{
    public function __construct(
        private readonly ExperimentalModelService $modelService,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->define("design")
            ->allowedTypes( ExperimentalDesign::class)
            ->required()
        ;
    }

    public function getParent()
    {
        return FormGroupType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $design = $options['design'];

        $environment = $this->modelService->getValidEnvironment($design);

        $builder
            ->add(
                "expression", ExpressionType::class, [
                    "label" => "Expression",
                    "required" => false,
                    "environment" => $environment,
                    "constraints" => [
                        new ValidExpression($environment),
                    ],
                ],
            )
        ;
    }
}