<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\DoctrineEntity\User\User;
use App\Form\BasicType\FancyEntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<User>
 */
class UserEntityType extends AbstractType
{
    public function getParent(): string
    {
        return FancyEntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "class" => User::class,
            "query_builder" => function (EntityRepository $er) {
                return $er->createQueryBuilder("e")
                    ->addOrderBy("e.fullName", "ASC")
                ;
            },
            "group_by" => function (User $user) {
                return $user->getIsActive() ? "Active" : "Inactive";
            },
            "empty_data" => null,
            "placeholder" => "Select a user",
            "multiple" => false,
            "allow_empty" => true,
        ]);
    }
}
