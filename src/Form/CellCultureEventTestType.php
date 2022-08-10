<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\DoctrineEntity\Cell\CellCultureTestEvent;
use App\Form\Traits\VocabularyTrait;
use App\Repository\VocabularyRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CellCultureEventTestType extends AbstractType
{
    use VocabularyTrait;

    public function __construct(
        private VocabularyRepository $vocabularyRepository,
    ) {

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("date", DateType::class, [

            ])
            ->add("shortName", TextType::class, [
                "label" => "Event name (required)",
                "help" => "A short name of the event.",
            ])
            ->add("description", TextareaType::class, [
                "label" => "Description",
                "help" => "A short description of the test.",
                "required" => false,
            ])
            ->add("result", ChoiceType::class, [
                "label" => "Test result",
                "help" => "Result of the test. Positive means that there is mycoplasma present.",
                "choices" => array_combine(CellCultureTestEvent::RESULTS, CellCultureTestEvent::RESULTS),
                "placeholder" => "Choose an option",
            ])
        ;

        $this->addTextOrChoiceType($builder, "testType", "mycoplasmaTests", [
            "label" => "Test type",
            "placeholder" => "Choose an option",
        ]);

        $builder->add("supernatantAmount", NumberType::class, [

        ]);

        if ($options["save_button"]) {
            $builder->add("save", SubmitType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => CellCultureTestEvent::class,
            "save_button" => false,
        ]);

        $resolver->setAllowedTypes("save_button", "bool");
        /*
        $resolver->setRequired([
            "experiment",
        ]);

        $resolver->setAllowedTypes("experiment", Experiment::class);*/
    }
}