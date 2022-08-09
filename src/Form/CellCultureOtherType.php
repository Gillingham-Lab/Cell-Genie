<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\DoctrineEntity\Cell\CellCultureSplittingEvent;
use App\Entity\DoctrineEntity\Cell\CellCultureTestEvent;
use App\Form\Traits\VocabularyTrait;
use App\Repository\VocabularyRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CellCultureOtherType extends AbstractType
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
        ;

        if ($options["save_button"]) {
            $builder->add("save", SubmitType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => CellCultureSplittingEvent::class,
            "save_button" => false,
        ]);

        $resolver->setAllowedTypes("save_button", "bool");
    }
}