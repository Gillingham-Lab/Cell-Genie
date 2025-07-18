<?php
declare(strict_types=1);

namespace App\Form\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Form\CompositeType\PrivacyAwareType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;

/**
 * @extends AbstractType<ExperimentalDesign>
 */
class ExperimentalDesignType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => ExperimentalDesign::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $design = $builder->getData();

        $builder
            ->add(
                $builder->create("_general", FormType::class, [
                    "inherit_data" => true,
                    "label" => "General",
                ])
                ->add("number", TextType::class, [
                    "label" => "Number",
                    "required"  => true,
                ])
                ->add("shortName", TextType::class, [
                    "label" => "Short Name",
                    "required"  => true,
                ])
                ->add("longName", TextType::class, [
                    "label" => "Long Name",
                    "required"  => true,
                ])
                ->add("ownership", PrivacyAwareType::class, [
                    "label" => "Ownership",
                    "required"  => true,
                    "inherit_data" => true,
                ]),
            )
            ->add(
                $builder->create("_fields", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Fields",
                ])
                ->add("fields", LiveCollectionType::class, [
                    "entry_type" => ExperimentalDesignFieldType::class,
                    "by_reference" => false,
                    "entry_options" => [
                        "design" => $design,
                    ],
                    "button_delete_options" => [
                        "attr" => [
                            "class" => "btn btn-outline-danger",
                        ],
                    ],
                    "button_add_options" => [
                        "attr" => [
                            "class" => "btn btn-outline-primary",
                        ],
                    ],
                ]),
            )
            ->add(
                $builder->create("_models", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Models",
                ])
                ->add(... $this->getModelsCollectionTypeParameters($design)),
            )
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, fn(FormEvent $event) => $this->onPreSetData($event, $design));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, fn(FormEvent $event) => $this->onPreSubmitData($event, $design));
    }

    public function onPreSetData(FormEvent $event, ?ExperimentalDesign $design): void
    {
        $form = $event->getForm();
        $formData = $event->getData();

        $modelChoices = [];
        if ($formData) {
            foreach ($formData->getModels() as $model) {
                $modelChoices[$model->getName()] = $model->getModel();
            }
        }

        $data = $form->get("_models")->get("models")->getData();

        $form->get("_models")->add(... $this->getModelsCollectionTypeParameters($design, $modelChoices));
        $form->get("_models")->get("models")->setData($data);
    }

    public function onPreSubmitData(FormEvent $event, ?ExperimentalDesign $design): void
    {
        $form = $event->getForm();
        $formData = $event->getData();
        $modelChoices = [];

        if (!isset($formData["_models"]) or !isset($formData["_models"]["models"])) {
            return;
        }

        foreach ($formData["_models"]["models"] as $model) {
            if (!(isset($model["name"]) and isset($model["model"]))) {
                continue;
            }

            $modelChoices[$model["name"]] = $model["model"];
        }

        $data = $form->get("_models")->get("models")->getData();

        $form->get("_models")->add(... $this->getModelsCollectionTypeParameters($design, $modelChoices));
        $form->get("_models")->get("models")->setData($data);
    }

    /**
     * @param array<string, string> $modelChoices
     * @return array{"models", class-string<LiveCollectionType>, array<string, mixed>}
     */
    private function getModelsCollectionTypeParameters(?ExperimentalDesign $design, array $modelChoices = []): array
    {
        return [
            "models", LiveCollectionType::class, [
                "entry_type" => ExperimentalModelType::class,
                "by_reference" => true,
                "entry_options" => [
                    "design" => $design,
                    "referenceModels" => $modelChoices,
                ],
                "button_delete_options" => [
                    "attr" => [
                        "class" => "btn btn-outline-danger",
                    ],
                ],
                "button_add_options" => [
                    "attr" => [
                        "class" => "btn btn-outline-primary",
                    ],
                ],
            ],
        ];
    }
}
