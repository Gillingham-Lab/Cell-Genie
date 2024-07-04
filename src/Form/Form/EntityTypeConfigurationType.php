<?php
declare(strict_types=1);

namespace App\Form\Form;

use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\DoctrineEntity\Substance\Oligo;
use App\Entity\DoctrineEntity\Substance\Plasmid;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\DoctrineEntity\User\User;
use App\Entity\DoctrineEntity\User\UserGroup;
use App\Entity\DoctrineEntity\Vendor;
use App\Entity\Lot;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class EntityTypeConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("entityType", ChoiceType::class, [
                "label" => "Entity type",
                "choices" => [
                    "Substances" => [
                        "Antibody" => Antibody::class,
                        "Chemical" => Chemical::class,
                        "Oligo" => Oligo::class,
                        "Plasmid" => Plasmid::class,
                        "Protein" => Protein::class,
                    ],
                    "Lots" => [
                        "Antibody Lot" => Antibody::class,
                        "Chemical Lot" => Chemical::class,
                        "Oligo Lot" => Oligo::class,
                        "Plasmid Lot" => Plasmid::class,
                        "Protein Lot" => Protein::class,
                    ],
                    "Other" => [
                        "User" => User::class,
                        "Group" => UserGroup::class,
                        "Vendor" => Vendor::class,
                    ],
                ],
            ])
        ;
    }
}