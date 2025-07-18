<?php
declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<array{file?: mixed, data?: ?string}>
 */
class CropImageType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "compound" => true,
            "empty_data" => [
                "data" => null,
            ],
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add("file");
        $builder->add("data");
        $builder->resetViewTransformers();
        $builder->addViewTransformer(new CallbackTransformer(
            function ($submittedData) {
                return [
                    "file" => null,
                    "data" => $submittedData,
                ];
            },
            function ($normalizedData) {
                return $normalizedData["data"] ?? "";
            },
        ));
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars["empty_data"] = ["data" => null];
    }

    public function getBlockPrefix(): string
    {
        return "crop_image";
    }
}
