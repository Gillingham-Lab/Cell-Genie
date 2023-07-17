<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\FormEntity\DetectionEntry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DetectionEntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("method", type: TextType::class, options: [
                "label" => "Detection method",
                "help" => "Which method has been used to detect this protein? Try to standarize the abbreviations. Examples: WB (Western blot), FACS, Shotgun proteomics, specific proteomics (note peptide).",
            ])
            ->add("isDetectable", type: CheckboxType::class, options: [
                "empty_data" => null,
                "label" => "Is detectable",
                "required" => false,
            ])
            ->add("comment", type: TextareaType::class, options: [
                "empty_data" => null,
                "help" => "A brief description how the protein was detected. Also give lab journal entries.",
                "required" => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DetectionEntry::class,
        ]);
    }
}