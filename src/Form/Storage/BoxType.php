<?php
declare(strict_types=1);

namespace App\Form\Storage;

use App\Entity\DoctrineEntity\Storage\Box;
use App\Entity\DoctrineEntity\Storage\Rack;
use App\Form\BasicType\FancyEntityType;
use App\Form\CompositeType\PrivacyAwareType;
use App\Form\SaveableType;
use Doctrine\ORM\EntityRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends SaveableType<Box>
 */
class BoxType extends SaveableType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                $builder->create("_general", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Box data",
                ])
                ->add("name", TextType::class, [
                    "label" => "Name of the box",
                    "help" => "5-255 characters; used to identify the box. No parent names.",
                    "required" => true,
                ])
                ->add("description", CKEditorType::class, [
                    "label" => "Description",
                    "sanitize_html" => true,
                    "required" => false,
                    "empty_data" => null,
                    "config" => ["toolbar" => "basic"],
                ])
                ->add("rows", IntegerType::class, [
                    "label" => "Number of rows in the box",
                    "required" => false,
                ])
                ->add("cols", IntegerType::class, [
                    "label" => "Number of columns in the box",
                    "required" => false,
                ])
                ->add("rack", FancyEntityType::class, [
                    "label" => "Location.",
                    "class" => Rack::class,
                    "group_by" => function (Rack $rack) { return ($rack->getMaxBoxes() > 0 && $rack->getBoxes()->count() >= $rack->getMaxBoxes()) ? "Full" : "Space available"; },
                    "choice_label" => function (Rack $rack) { return $rack->getPathName(); },
                    "choice_value" => function (?Rack $rack) { return $rack?->getUlid()?->toBase58(); },
                    "query_builder" => function (EntityRepository $er) {
                        return $er->createQueryBuilder("r")
                            ->select("r")
                            ->addSelect("b")
                            ->leftJoin("r.boxes", "b")
                            ->groupBy("r.ulid")
                            ->addGroupBy("b.ulid")
                        ;
                    },
                    "placeholder" => "Empty",
                    "required" => false,
                    "allow_empty" => true,
                ])
                ->add("_privacy", PrivacyAwareType::class, [
                    "inherit_data" => true,
                    "label" => "Ownership",
                ]),
            )
        ;

        parent::buildForm($builder, $options);
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => Box::class,
        ]);

        parent::configureOptions($resolver);
    }
}
