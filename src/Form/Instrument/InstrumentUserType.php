<?php
declare(strict_types=1);

namespace App\Form\Instrument;

use App\Entity\DoctrineEntity\InstrumentUser;
use App\Entity\DoctrineEntity\User\User;
use App\Form\BasicType\FancyEntityType;
use App\Genie\Enums\InstrumentRole;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<InstrumentUser>
 */
class InstrumentUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("user", FancyEntityType::class, options: [
                "label" => "User",
                "class" => User::class,
                "query_builder" => function (EntityRepository $er) {
                    return $er->createQueryBuilder("a")
                        ->addOrderBy("a.fullName", "ASC")
                        ->where("a.isActive = true");
                },
                "allow_empty" => true,
                "group_by" => function (User $instrumentUser) {
                    return $instrumentUser->getGroup();
                },
                'empty_data' => null,
                'by_reference' => true,
                "multiple" => false,
                "required" => false,
                "placeholder" => "Empty",
            ])
            ->add("role", EnumType::class, options: [
                "label" => "Role",
                "class" => InstrumentRole::class,
                "help" => "What Role should the user have?",
                "required" => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => InstrumentUser::class,
        ]);

        parent::configureOptions($resolver);
    }
}