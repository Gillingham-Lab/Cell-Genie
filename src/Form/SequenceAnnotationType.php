<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\SequenceAnnotation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<SequenceAnnotation>
 */
class SequenceAnnotationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("annotationLabel", TextType::class, options: [
                "label" => "Label",
                "required" => true,
            ])
            ->add("annotationType", TextType::class, options: [
                "label" => "Annotation type",
                "help" => "Type of the annotation. Can be any text, but common terms are preferred ('CDS' for coding sequence, 'primer', 'Promoter' or 'misc_feature').",
                "required" => true,
            ])
            ->add("color", ColorType::class, options: [
                "label" => "Feature colour",
                "required" => false,
                "empty_data" => "#000000",
                "html5" => true,
            ])
            ->add("isComplement", CheckboxType::class, options: [
                "label" => "Is the feature on the opposite strand?",
                "required" => false,
            ])
            ->add("annotationStart", IntegerType::class, options: [
                "label" => "Feature start point (first basepair = 1)",
            ])
            ->add("annotationEnd", IntegerType::class, options: [
                "label" => "Feature end point (last basepair included).",
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SequenceAnnotation::class,
        ]);
    }
}
