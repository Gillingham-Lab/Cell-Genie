<?php
declare(strict_types=1);

namespace App\Form\Substance;

use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\Epitope;
use App\Form\BasicType\EnumeratedType;
use App\Form\BasicType\FancyEntityType;
use App\Form\Collection\AttachmentCollectionType;
use App\Form\CompositeType\PrivacyAwareType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends SubstanceType<Chemical>
 */
class ChemicalType extends SubstanceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                $builder->create("general", FormType::class, [
                    "inherit_data" => true,
                    "label" => "General information"
                ])
                ->add("shortName", EnumeratedType::class, [
                    "label" => "Short name",
                    "help" => "Short name of the chemical, must be unique among all substances.",
                    "required" => true,
                    "enumeration_type" => "chemical",
                ])
                ->add("longName", TextType::class, [
                    "label" => "Name",
                    "help" => "A longer, more descriptive name.",
                ])
                ->add("iupacName", TextType::class, [
                    "label" => "IUPAC name",
                    "help" => "Systematic name according to IUPAC rules (or whatever ChemDraw generates)."
                ])
                ->add("casNumber", TextType::class, [
                    "label" => "CAS number",
                    "required" => false,
                ])
                ->add("labjournal", UrlType::class, [
                    "label" => "Lab journal",
                    "help" => "A link to a lab journal entry, for synthesis reference.",
                    "required" => false,
                ])
                ->add("_privacy", PrivacyAwareType::class, [
                    "inherit_data" => true,
                    "label" => "Ownership",
                ])
            )
            ->add(
                $builder->create("structure", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Structure",
                ])
                ->add("smiles", TextType::class, [
                    "label" => "SMILES",
                    "help" => "The SMILES representation of the structure. Use a tool such as ChemDraw to paste the structure.",
                    "required" => false,
                    "empty_data" => "",
                ])
                ->add("molecularMass", NumberType::class, [
                    "label" => "Molecular mass [Da]",
                    "help" => "Molecular mass of the structure.",
                    "scale" => 3,
                    "required" => false,
                ])
                ->add("density", NumberType::class, [
                    "label" => "Density [g/L]",
                    "help" => "The density of the compound. If given, recipes requiring this chemical will also provide a volume.",
                    "scale" => 3,
                    "required" => false,
                ])
                ->add("epitopes", FancyEntityType::class, [
                    "class" => Epitope::class,
                    "query_builder" => function (EntityRepository $er) {
                        return $er->createQueryBuilder("e")
                            ->addOrderBy("e.shortName", "ASC")
                        ;
                    },
                    'empty_data' => [],
                    'by_reference' => false,
                    "placeholder" => "Empty",
                    "required" => false,
                    "multiple" => true,
                    "allow_empty" => true,
                ])
            )
            ->add(
                $builder->create("_attachments", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Attachments",
                ])
                ->add("attachments", AttachmentCollectionType::class, [
                    "label" => "Attachments",
                ])
            )
        ;

        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => Chemical::class,
        ]);

        parent::configureOptions($resolver);
    }
}