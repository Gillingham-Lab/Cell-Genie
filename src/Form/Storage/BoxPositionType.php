<?php
declare(strict_types=1);

namespace App\Form\Storage;

use App\Entity\DoctrineEntity\Storage\Box;
use App\Form\BasicType\FancyEntityType;
use App\Form\BasicType\FormGroupType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<array{box: ?Box, boxCoordinate: ?string}>
 */
class BoxPositionType extends AbstractType
{
    public function getParent(): string
    {
        return FormGroupType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("box", FancyEntityType::class, options: [
                "class" => Box::class,
                "label" => "Box",
                "help" => "Which box is the Aliquot located in",

                "query_builder" => function (EntityRepository $er) {
                    return $er->createQueryBuilder("b")
                        ->addOrderBy("b.name", "ASC");
                },
                "group_by" => function (Box $choice, $key, $value) {
                    return ($choice->getRack());
                },
                'empty_data' => null,
                "placeholder" => "Empty",
                "required" => false,
                "allow_empty" => true,
            ])
            ->add("boxCoordinate", TextType::class, options: [
                "label" => "Position in box",
                "help" => "Give the position in the box. Use letters for row, and numbers for column (A12 is the first row, 12th column; AA1 is the 27th row, 1st column)",
                "required" => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault("inherit_data", true);
        $resolver->setDefault("label", "Storage location");
        $resolver->setDefault("icon", "box");
    }
}
