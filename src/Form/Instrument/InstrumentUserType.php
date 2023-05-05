<?php
declare(strict_types=1);

namespace App\Form\Instrument;

use App\Entity\DoctrineEntity\InstrumentUser;
use App\Entity\DoctrineEntity\User\User;
use App\Genie\Enums\InstrumentRole;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InstrumentUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("user", EntityType::class, options: [
                "label" => "File title (not necessarily a file name)",
                "class" => User::class,
                "query_builder" => function (EntityRepository $er) {
                    return $er->createQueryBuilder("a")
                        ->addOrderBy("a.fullName", "ASC")
                        ->where("a.isActive = true");
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
            ->add("role", ChoiceType::class, options: [
                "label" => "Type",
                "help" => "Mark if the antibody is primary or secondary",
                "required" => true,
                "choices" => [
                    "Untrained" => InstrumentRole::Untrained,
                    "Trained" => InstrumentRole::Trained,
                    "User" => InstrumentRole::User,
                    "Advanced" => InstrumentRole::Advanced,
                    "Responsible" => InstrumentRole::Responsible,
                    "Admin" => InstrumentRole::Admin,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => InstrumentUser::class,
        ]);

        parent::configureOptions($resolver);
    }
}