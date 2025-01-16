<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\DoctrineEntity\User\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserEntityType extends EntityType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

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
            "attr"  => [
                "class" => "gin-fancy-select",
                "data-allow-empty" => "true",
            ],
        ]);
    }
}