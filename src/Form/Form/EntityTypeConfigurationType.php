<?php
declare(strict_types=1);

namespace App\Form\Form;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Cell\CellAliquot;
use App\Entity\DoctrineEntity\Lot;
use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\DoctrineEntity\Substance\Oligo;
use App\Entity\DoctrineEntity\Substance\Plasmid;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\DoctrineEntity\User\User;
use App\Entity\DoctrineEntity\User\UserGroup;
use App\Entity\DoctrineEntity\Vendor;
use App\Form\BasicType\FormGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @template TData
 * @extends AbstractType<TData>
 */
class EntityTypeConfigurationType extends AbstractType
{
    public function getParent(): string
    {
        return FormGroupType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("entityType", ChoiceType::class, [
                "label" => "Entity type",
                "choices" => [
                    "Cells" => [
                        "Cell line" => Cell::class,
                        "Cell aliquot" => CellAliquot::class,
                    ],
                    "Substances" => [
                        "Antibody" => Antibody::class,
                        "Chemical" => Chemical::class,
                        "Oligo" => Oligo::class,
                        "Plasmid" => Plasmid::class,
                        "Protein" => Protein::class,
                    ],
                    "Lots" => [
                        "Antibody Lot" => Lot::class . "|" . Antibody::class,
                        "Chemical Lot" => Lot::class . "|" . Chemical::class,
                        "Oligo Lot" => Lot::class . "|" . Oligo::class,
                        "Plasmid Lot" => Lot::class . "|" . Plasmid::class,
                        "Protein Lot" => Lot::class . "|" . Protein::class,
                    ],
                    "Other" => [
                        "User" => User::class,
                        "Group" => UserGroup::class,
                        "Vendor" => Vendor::class,
                    ],
                ],
                "required" => true,
            ])
        ;
    }
}
