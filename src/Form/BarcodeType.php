<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Cell\CellCulture;
use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\DoctrineEntity\Substance\Oligo;
use App\Entity\DoctrineEntity\Substance\Plasmid;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Entity\FormEntity\BarcodeEntry;
use App\Entity\Lot;
use App\Entity\SubstanceLot;
use App\Repository\LotRepository;
use App\Repository\Substance\SubstanceRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BarcodeType extends AbstractType
{
    public function __construct(
        private LotRepository $lotRepository,
        private SubstanceRepository $substanceRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("barcode", TextType::class, [
                "disabled" => true,
            ])
            ->add("cellCulture", EntityType::class, [
                "class" => CellCulture::class,
                'empty_data' => null,
                "query_builder" => function(EntityRepository $er) {
                    return $er->createQueryBuilder("cc")
                        ->addSelect("co")
                        ->addSelect("ce")
                        ->addSelect("c")
                        ->addSelect("ca")
                        ->leftJoin("cc.owner", "co", conditionType: Join::ON)
                        ->leftJoin("cc.events", "ce", conditionType: Join::ON)
                        ->leftJoin("cc.aliquot", "ca", conditionType: Join::ON)
                        ->leftJoin("ca.cell", "c", conditionType: Join::ON)
                        ->addGroupBy("cc.id")
                        ->addGroupBy("co.id")
                        ->addGroupBy("ce.id")
                        ->addGroupBy("c.id")
                        ->addGroupBy("ca.id")
                        ->addSelect("CASE WHEN cc.trashedOn IS NULL THEN 1 ELSE 0 END AS HIDDEN priority")
                        ->addOrderBy("co.fullName", "ASC")
                        ->addOrderBy("priority", "DESC")
                        ->addOrderBy("cc.number", "ASC")
                        ->where("cc.trashedOn > :timepoint")
                        ->orWhere("cc.trashedOn IS NULL")
                        ->setParameter("timepoint", new \DateTime("now - 1 week"))
                        ;
                },
                "placeholder" => "Empty",
                "required" => false,
                "attr"  => [
                    "class" => "gin-fancy-select",
                    "data-allow-empty" => "true",
                ],
                "group_by" => function($choice, $key, $value) {
                    return $choice->getOwner();
                },
            ])
            ->add("cell", EntityType::class, [
                "class" => Cell::class,
                "query_builder" => function (EntityRepository $er) {
                    return $er->createQueryBuilder("c")
                        ->addOrderBy("c.cellNumber", "ASC")
                        ->addOrderBy("c.name", "ASC");
                },
                'empty_data' => null,
                "placeholder" => "Empty",
                "required" => false,
                "attr"  => [
                    "class" => "gin-fancy-select",
                    "data-allow-empty" => "true",
                ],
            ])
            ->add("substance", EntityType::class, [
                "class" => Substance::class,
                "query_builder" => function (EntityRepository $er) {
                    return $er->createQueryBuilder("s")
                        ->addOrderBy("s.shortName", "ASC")
                    ;
                },
                'empty_data' => null,
                "placeholder" => "Empty",
                "required" => false,
                "attr"  => [
                    "class" => "gin-fancy-select",
                    "data-allow-empty" => "true",
                ],
                "group_by" => function($choice, $key, $value) {
                    return match($choice::class) {
                        Antibody::class => "Antibodies",
                        Chemical::class => "Chemicals",
                        Oligo::class => "Oligos",
                        Protein::class => "Proteins",
                        Plasmid::class => "Plasmids",
                        default => "Other",
                    };
                },
            ])
            ->add("substanceLot", ChoiceType::class, [
                "choices" => $this->getLotChoices(),
                "choice_label" => function ($choice) {
                    if ($choice?->getSubstance() instanceof Antibody) {
                        return $choice?->getSubstance()?->getNumber() . "." . $choice?->getLot()?->getNumber() . " (" . $choice?->getLot()?->getLotNumber() .")";
                    } else {
                        return $choice?->getSubstance()?->getShortName() . "." . $choice?->getLot()?->getNumber() . " (" . $choice?->getLot()?->getLotNumber() .")";
                    }
                },
                "choice_value" => function ($choice) {
                    return $choice?->getLot()?->getId();
                },
                'empty_data' => null,
                "placeholder" => "Empty",
                "required" => false,
                "attr"  => [
                    "class" => "gin-fancy-select",
                    "data-allow-empty" => "true",
                ],
                "group_by" => function($choice, $key, $value) {
                    return is_null($choice) ? "Other" : match(($choice->getSubstance())::class) {
                        Antibody::class => "Antibodies",
                        Chemical::class => "Chemicals",
                        Oligo::class => "Oligos",
                        Protein::class => "Proteins",
                        Plasmid::class => "Plasmids",
                        default => "Other",
                    };
                },
            ])
        ;

        if ($options["save_button"]) {
            $builder->add("save", SubmitType::class);
        }
    }

    private function getLotChoices()
    {
        $substances = $this->substanceRepository->findAllWithLot();

        $choices = [];

        foreach ($substances as $substance) {
            foreach ($substance->getLots() as $lot) {
                $choices[] = new SubstanceLot($substance, $lot);
            }
        }

        return $choices;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => BarcodeEntry::class,
            "save_button" => false,
        ]);

        $resolver->setAllowedTypes("save_button", "bool");
    }
}