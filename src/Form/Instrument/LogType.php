<?php
declare(strict_types=1);

namespace App\Form\Instrument;

use App\Entity\DoctrineEntity\Log;
use App\Form\CompositeType\PrivacyAwareType;
use App\Form\SaveableType;
use App\Genie\Enums;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends SaveableType<Log>
 */
class LogType extends SaveableType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("title")
            ->add("description", CKEditorType::class, [

            ])
            ->add("logType", EnumType::class, [
                "class" => Enums\LogType::class
            ])
            ->add("_privacy", PrivacyAwareType::class, [
                "inherit_data" => true,
            ])
        ;

        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            "data_class" => Log::class
        ]);
    }
}