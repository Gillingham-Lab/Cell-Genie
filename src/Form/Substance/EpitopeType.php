<?php
declare(strict_types=1);

namespace App\Form\Substance;

use App\Entity\DoctrineEntity\Epitope;
use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\DoctrineEntity\Substance\Oligo;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Form\BasicType\FancyEntityType;
use App\Form\SaveableType;
use Doctrine\ORM\EntityRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends SaveableType<Epitope>
 */
class EpitopeType extends SaveableType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("shortName", TextType::class, [
                "label" => "Name",
                "help" => "(Unique) epitope name",
                "required" => true,
            ])
            ->add("description", CKEditorType::class, [
                "label" => "Description",
                "help" => "Give some information about the epitope (if known).",
                "sanitize_html" => true,
                "required" => false,
                "empty_data" => null,
                "config" => ["toolbar" => "basic"],
            ])
            ->add("substances", FancyEntityType::class, [
                "label" => "Substances with this epitope",
                "class" => Substance::class,
                "query_builder" => function (EntityRepository $er) {
                    return $er->createQueryBuilder("e")
                        ->addOrderBy("e.shortName", "ASC")
                    ;
                },
                "group_by" => function ($choice, $key, $value) {
                    return match ($choice::class) {
                        Antibody::class => "Antibodies",
                        Chemical::class => "Chemicals",
                        Oligo::class => "Oligos",
                        Protein::class => "Proteins",
                        default => "Other",
                    };
                },
                'empty_data' => [],
                'by_reference' => false,
                "placeholder" => "Empty",
                "required" => false,
                "multiple" => true,
                "allow_empty" => true,
            ])
            ->add("antibodies", FancyEntityType::class, [
                "label" => "Antibodies targeting this epitope",
                "class" => Antibody::class,
                "query_builder" => function (EntityRepository $er) {
                    return $er->createQueryBuilder("e")
                        ->addOrderBy("e.number", "ASC")
                    ;
                },
                'empty_data' => [],
                'by_reference' => false,
                "placeholder" => "Empty",
                "required" => false,
                "multiple" => true,
                "allow_empty" => true,
            ])
        ;

        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => Epitope::class,
        ]);

        parent::configureOptions($resolver);
    }
}
