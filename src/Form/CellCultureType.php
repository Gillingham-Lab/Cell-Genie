<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\DoctrineEntity\Cell\CellCulture;
use App\Entity\DoctrineEntity\Cell\CellCultureSplittingEvent;
use App\Form\Traits\VocabularyTrait;
use App\Repository\VocabularyRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CellCultureType extends AbstractType
{
    use VocabularyTrait;

    public function __construct(
        private VocabularyRepository $vocabularyRepository,
    ) {

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("number", TextType::class, [
                "label" => "Culture number",
                "help" => "A short (max 10) identifier of the cell culture, like FLC001 (First name, Last name, Cell).",
            ])
            ->add("unfrozenOn", DateType::class, [
                "label" => "Culture start",
                "format" => "dd MMM yyyy",
            ])
            ->add("trashedOn", DateType::class, [
                "label" => "Culture trashed",
                "format" => "dd MMM yyyy",
                "help" => "Choosing a date before culture start equals to a culture that has not been trashed, yet.",
                "empty_data" => null,
            ])
        ;

        $this->addTextOrChoiceType($builder, "incubator", "cellCultureIncubator", [
            "label" => "Incubator",
        ]);

        $this->addTextOrChoiceType($builder, "flask", "cellCultureFlasks", [
            "label" => "Flask type",
        ]);

        if ($options["save_button"]) {
            $builder->add("save", SubmitType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => CellCulture::class,
            "save_button" => false,
        ]);

        $resolver->setAllowedTypes("save_button", "bool");
    }
}