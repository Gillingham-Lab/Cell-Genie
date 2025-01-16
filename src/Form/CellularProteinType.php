<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\DoctrineEntity\Cell\CellProtein;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<CellProtein>
 */
class CellularProteinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("associatedProtein", options: [
                "placeholder" => "Choose a protein",
                "label" => "Protein",
                "help" => "Add the corresponding protein",
            ])
            ->add("description", options: [
                "help" => "Context or details about the protein."
            ])
            ->add("detection", type: CollectionType::class, options: [
                "allow_add" => true,
                "allow_delete" => true,
                "by_reference" => false,
                "entry_type" => DetectionEntryType::class,
                "label" => "Detectable",
                "help" => "Add experiments which have been confirmed that the protein is present."
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CellProtein::class,
            "by_reference" => false,
        ]);
    }
}