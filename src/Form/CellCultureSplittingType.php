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

class CellCultureSplittingType extends AbstractType
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
                'widget' => 'single_text',
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
            ->add("splitting", TextType::class, [
                "label" => "Splitting (required)",
                "help" => "Small detail on how you split the cells (eg, 'reseeded with 5% of cells', 'seeded with 100K cells'). Also mention atypical medium not mentioned in the cell's description.",
            ])
        ;

        if ($options["show_splits"]) {
            $builder->add("newCultures", IntegerType::class, [
                "label" => "Number of new cultures",
                "help" => "Set to any number above 0 to create that many new cultures. Use with care. Any number above 10 will be ignored (use well-plates for that).",
                "mapped" => false,
                "data" => 0,
            ]);
        }

        $this->addTextOrChoiceType($builder, "newFlask", "cellCultureFlasks", [
            "label" => "Flask type",
        ]);

        if ($options["save_button"]) {
            $builder->add("save", SubmitType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => CellCultureSplittingEvent::class,
            "show_splits" => false,
            "save_button" => false,
        ]);

        $resolver->setAllowedTypes("show_splits", "bool");
        $resolver->setAllowedTypes("save_button", "bool");
    }
}