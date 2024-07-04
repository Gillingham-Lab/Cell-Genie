<?php
declare(strict_types=1);

namespace App\Form\Instrument;

use App\Entity\DoctrineEntity\Instrument;
use App\Entity\DoctrineEntity\StockManagement\Consumable;
use App\Form\Collection\AttachmentCollectionType;
use App\Form\NameType;
use App\Form\SaveableType;
use App\Form\User\PrivacyAwareType;
use App\Repository\Instrument\InstrumentRepository;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\TextEditorType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InstrumentType extends SaveableType
{

    public function __construct(
        private InstrumentRepository $instrumentRepository,
    ) {

    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $entity = $builder->getData();

        $builder
            ->add(
                $builder->create("general", NameType::class, [
                    "inherit_data" => true,
                    "label" => "General information",
                ])
                ->add("_privacy", PrivacyAwareType::class, [
                    "inherit_data" => true,
                    "label" => "Ownership",
                ])
                ->add("instrumentNumber", TextType::class, [
                    "label" => "Instrument Number",
                    "help" => "An internal number for referencing the instrument. Please use the following naming scheme: XX-YY-ZZ, where X notes a instrument type (LC for HPLCs, PC for PCR), YY denotes the vendor (AG for Agilent, TF for Thermo Fisher) and ZZ an increasing number.",
                ])
                ->add("location", TextType::class, [
                    "label" => "Location",
                    "help" => "Where is the machine located?",
                    "required" => false,
                ])
                ->add("registrationNumber", TextType::class, [
                    "label" => "Registration Number",
                    "help" => "The internal registration number of the machine (the number on the university sticker if applicable)",
                    "required" => false,
                ])
                ->add("description", CKEditorType::class, [
                    "label" => "Description",
                    "help" => "Write down a description of the instrument and instrument-specific details.",
                    "sanitize_html" => true,
                    "required" => false,
                    "empty_data" => null,
                    "config" => ["toolbar" => "basic"],
                ])
                ->add("requiresReservation", CheckboxType::class, [
                    "label" => "Requires reservation?",
                    "help" => "Check if the instrument requires a reservation.",
                    "required" => false,
                    "empty_data" => null,
                ])
                ->add("requiresTraining", CheckboxType::class, [
                    "label" => "Requires training?",
                    "help" => "Check if the instrument requires training.",
                    "required" => false,
                    "empty_data" => null,
                ])
                ->add("modular", CheckboxType::class, [
                    "label" => "Is the instrument modular",
                    "help" => "Check if the instrument consists of modular parts, each with their own registered part.",
                    "required" => false,
                    "empty_data" => null,
                ])
                ->add("collective", CheckboxType::class, [
                    "label" => "Is the entry a collective-type?",
                    "help" => "Check if the instrument summarizes multiple machines of the same type (each with their own registered part)",
                    "required" => false,
                    "empty_data" => null,
                ])
                ->add("parent", EntityType::class, [
                    "label" => "Part of",
                    "help" => "Select the instrument that this entry is a part of. Only works with modular or collective instruments.",
                    "class" => Instrument::class,
                    "query_builder" => function (EntityRepository $er) use ($entity) {
                        $qb = $er->createQueryBuilder("i")
                            ->addOrderBy("i.instrumentNumber", "ASC")
                            ->where("(i.collective = true or i.modular = true)")
                            ;

                        if ($entity->getId()) {
                            $qb = $qb->andWhere("i.id != :ulid")
                                ->setParameter("ulid", $entity->getId(), "ulid");
                        }

                        return $qb;
                    },
                    "attr"  => [
                        "class" => "gin-fancy-select",
                        "data-allow-empty" => "true",
                    ],
                    'empty_data' => null,
                    'by_reference' => true,
                    "multiple" => false,
                    "required" => false,
                    "placeholder" => "Empty",
                ])
                ->add("children", EntityType::class, [
                    "label" => "Modules / Instruments",
                    "help" => "Select instruments that are part of this instrument.",
                    "class" => Instrument::class,
                    "query_builder" => function (EntityRepository $er) use ($entity) {
                        $qb = $er->createQueryBuilder("i")
                            ->where("(i.collective = false and i.modular = false)")
                            ->addOrderBy("i.instrumentNumber", "ASC")
                        ;

                        if ($entity->getId()) {
                            $qb = $qb->andWhere("i.id != :ulid")
                                ->andWhere("(i.parent is null or i.parent = :ulid)")
                                ->setParameter("ulid", $entity->getId(), "ulid");
                        } else {
                            $qb = $qb->andWhere("i.parent is null");
                        }

                        return $qb;
                    },
                    'empty_data' => [],
                    'by_reference' => false,
                    "placeholder" => "Empty",
                    "required" => false,
                    "multiple" => true,
                    "attr"  => [
                        "class" => "gin-fancy-select",
                        "data-allow-empty" => "true",
                    ],
                ])
            )
            ->add(
                $builder->create("details", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Detailed information",
                ])
                ->add("modelNumber", TextType::class, [
                    "label" => "Model Number",
                    "help" => "The product or model number of the instrument",
                ])
                ->add("serialNumber", TextType::class, [
                    "label" => "Serial Number",
                    "help" => "The serial number of the product",
                ])
                ->add("instrumentContact", EmailType::class, [
                    "label" => "Contact",
                    "help" => "A contact address of the company",
                    "required" => false,
                ])
                ->add("lastMaintenance", DateType::class, [
                    "label" => "Last Maintenance",
                    "help" => "When was the last maintenance done?",
                    "required" => false,
                    "html5" => true,
                    "empty_data" => "",
                    "placeholder" => "Set a date",
                    "widget" => "single_text",
                ])
                ->add("acquiredOn", DateType::class, [
                    "label" => "Acquired on",
                    "help" => "When did we get this specific cell line?",
                    "required" => false,
                    "html5" => true,
                    "empty_data" => "",
                    "placeholder" => "Set a date",
                    "widget" => "single_text",
                ])
                ->add("citationText", TextEditorType::class, [
                    "label" => "Citation Text",
                    "help" => "Write down a text how the instrument should be described in supporting information.",
                    "required" => false,
                    "empty_data" => null,
                ])
                ->add("consumables", EntityType::class, [
                    "label" => "Consumables",
                    "help" => "Select consumables used by this instrument.",
                    "class" => Consumable::class,
                    "query_builder" => function (EntityRepository $er) {
                        return $er->createQueryBuilder("c")
                            ->addSelect("cc")
                            ->leftJoin("c.category", "cc")
                            ->addOrderBy("c.longName", "ASC")
                        ;
                    },
                    "group_by" => function (Consumable $consumable) {
                        return $consumable->getCategory()->getLongName();
                    },
                    'empty_data' => [],
                    'by_reference' => false,
                    "placeholder" => "Empty",
                    "required" => false,
                    "multiple" => true,
                    "attr"  => [
                        "class" => "gin-fancy-select",
                        "data-allow-empty" => "true",
                    ],
                ])
            )
            ->add(
                $builder->create("_booking", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Booking",
                ])
                ->add("defaultReservationLength", NumberType::class, [
                    "label" => "Default reservation time, in hours",
                    "required" => true,
                    "empty_data" => null,
                ])
                ->add("calendarId", EmailType::class, [
                    "label" => "Google calendar ID",
                    "help" => "Google calendar id (format: id@group.calendar.google.com) to embed the specific calendar",
                    "required" => false,
                    "empty_data" => null,
                ])
                ->add("authString", TextareaType::class, [
                    "label" => "Google API auth string",
                    "help" => "A google API auth string for a service account. The calendar must be shared with writing access with the service account given in the auth string.",
                    "required" => false,
                    "empty_data" => null,
                ])
            )
            ->add(
                $builder->create("users", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Users"
                ])
                ->add("users", InstrumentUserCollectionType::class, [
                    "label" => "Users",
                    "help" => "Configure users and their levels",
                    "required" => false,
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
            "data_class" => Instrument::class,
        ]);

        parent::configureOptions($resolver);
    }
}