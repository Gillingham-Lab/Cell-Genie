<?php
declare(strict_types=1);

namespace App\Form\StockKeeping;

use App\Entity\DoctrineEntity\StockManagement\Consumable;
use App\Entity\DoctrineEntity\StockManagement\ConsumableCategory;
use App\Form\BasicType\FancyEntityType;
use App\Form\CompositeType\PrivacyAwareType;
use App\Form\LongNameType;
use App\Form\SaveableType;
use Doctrine\ORM\EntityRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends SaveableType<ConsumableCategory>
 */
class ConsumableCategoryType extends SaveableType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $entity = $builder->getData();

        $builder
            ->add(
                $builder->create("_general", LongNameType::class, [
                    "inherit_data" => true,
                    "label" => "General information",
                ])
                ->add("comment", CKEditorType::class, [
                    "label" => "Comment",
                    "sanitize_html" => true,
                    "required" => false,
                    "empty_data" => null,
                    "config" => ["toolbar" => "basic"],
                ])
                ->add("showUnits", CheckboxType::class, [
                    "label" => "Show total units of consumables?",
                    "help" => "If activated, the entry will show a total of available units for all consumables in this 
                        category. Activating this is helpful if the category contains consumable from different vendors, 
                        but with similar purpose (eg, 1.5 mL microcentrifuge tubes) where brand doesn't matter.",
                    "required" => false,
                    "empty_data" => null,
                ])
                ->add("idealStock", NumberType::class, [
                    "label" => "Ideal stock",
                    "help" => "If the option 'consume package' is turned on, the warning is displayed if the number of packages is less or equal that number. If not, it is the number of pieces.",
                ])
                ->add("orderLimit", NumberType::class, [
                    "label" => "Minimum number before ordering is recommended",
                    "help" => "If the option 'consume package' is turned on, the warning is displayed if the number of packages is less or equal that number. If not, it is the number of pieces.",
                ])
                ->add("criticalLimit", NumberType::class, [
                    "label" => "Absolute minimum before ordering is required",
                    "help" => "If the option 'consume package' is turned on, the warning is displayed if the number of packages is less or equal that number. If not, it is the number of pieces.",
                ])
                ->add("consumables", FancyEntityType::class, [
                    "label" => "Consumables",
                    "help" => "Select consumables to be part of this category.",
                    "class" => Consumable::class,
                    "query_builder" => function (EntityRepository $er) use ($entity) {
                        $qb = $er->createQueryBuilder("c")
                            ->addOrderBy("c.longName", "ASC")
                        ;

                        if ($entity->getId()) {
                            $qb = $qb->andWhere("c.id != :ulid")
                                ->setParameter("ulid", $entity->getId(), "ulid");
                        }

                        return $qb;
                    },
                    'empty_data' => [],
                    'by_reference' => false,
                    "placeholder" => "Empty",
                    "required" => false,
                    "multiple" => true,
                    "allow_empty" => true,
                ])
                ->add("parent", FancyEntityType::class, [
                    "label" => "Parent",
                    "help" => "Select a parent category.",
                    "class" => ConsumableCategory::class,
                    "query_builder" => function (EntityRepository $er) use ($entity) {
                        $qb = $er->createQueryBuilder("cc")
                            ->addOrderBy("cc.longName", "ASC")
                        ;

                        if ($entity->getId()) {
                            $qb = $qb->andWhere("cc.id != :ulid")
                                ->setParameter("ulid", $entity->getId(), "ulid");
                        }

                        return $qb;
                    },
                    "allow_empty" => true,
                    'empty_data' => null,
                    'by_reference' => true,
                    "multiple" => false,
                    "required" => false,
                    "placeholder" => "Empty",
                ])
                ->add("children", FancyEntityType::class, [
                    "label" => "Child categories",
                    "help" => "Select categories that are part of this one.",
                    "class" => ConsumableCategory::class,
                    "query_builder" => function (EntityRepository $er) use ($entity) {
                        $qb = $er->createQueryBuilder("cc")
                            ->addOrderBy("cc.longName", "ASC")
                        ;

                        if ($entity->getId()) {
                            $qb = $qb->andWhere("cc.id != :ulid")
                                ->setParameter("ulid", $entity->getId(), "ulid");
                        }

                        return $qb;
                    },
                    'empty_data' => [],
                    'by_reference' => false,
                    "placeholder" => "Empty",
                    "required" => false,
                    "multiple" => true,
                    "allow_empty" => true,
                ])
                ->add("_ownership", PrivacyAwareType::class, [
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
            "data_class" => ConsumableCategory::class,
        ]);

        parent::configureOptions($resolver);
    }
}
