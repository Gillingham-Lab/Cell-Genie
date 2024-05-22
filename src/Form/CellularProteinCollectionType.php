<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\DoctrineEntity\Cell\CellProtein;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Genie\Enums\GeneRegulation;
use Doctrine\DBAL\Types\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;
use Tienvx\UX\CollectionJs\Form\CollectionJsType;

class CellularProteinCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("associatedProtein", options: [
                "placeholder" => "Choose a protein",
                "label" => "Protein",
                "help" => "Add the corresponding protein",
            ])
            ->add("geneRegulation", EnumType::class, [
                "label" => "Gene regulation",
                "class" => GeneRegulation::class,
            ])
            ->add("description", options: [
                "help" => "Context or details about the protein."
            ])
            ->add("detection", type: CollectionJsType::class, options: [
                "prototype" => true,
                "allow_add" => true,
                "allow_delete" => true,
                "allow_move_up" => true,
                "allow_move_down" => true,
                "delete_empty" => true,
                "by_reference" => false,
                "entry_type" => DetectionEntryType::class,
                "label" => "Detectable",
                "help" => "Add experiments which have been confirmed that the protein is present.",
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