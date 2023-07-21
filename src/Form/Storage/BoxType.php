<?php
declare(strict_types=1);

namespace App\Form\Storage;

use App\Entity\Box;
use App\Entity\Rack;
use App\Form\SaveableType;
use App\Repository\RackRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BoxType extends SaveableType
{
    public function __construct(
        private RackRepository $rackRepository,
    ) {

    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
            ->add("rack", EntityType::class, [
                "label" => "Location.",
                "class" => Rack::class,
                "group_by" => function(Rack $rack) { return ($rack->getMaxBoxes() > 0 && $rack->getBoxes()->count() >= $rack->getMaxBoxes()) ? "Full" : "Space available"; },
                "choice_label" => function(Rack $rack) { return $rack->getPathName(); },
                "choice_value" => function(?Rack $rack) { return $rack?->getUlid()?->toBase58(); },
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
                "attr"  => [
                    "class" => "gin-fancy-select",
                    "data-allow-empty" => "true",
                ],
            ])
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