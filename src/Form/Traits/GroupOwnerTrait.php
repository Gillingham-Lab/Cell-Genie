<?php
declare(strict_types=1);

namespace App\Form\Traits;

use App\Entity\DoctrineEntity\User\User;
use App\Entity\DoctrineEntity\User\UserGroup;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormBuilderInterface;

trait GroupOwnerTrait
{
    private function addGroupOwnerField(FormBuilderInterface $builder, Security $security): void
    {
        $builder->add("group", EntityType::class, [
            "label" => "Group owner",
            "required" => false,
            "class" => UserGroup::class,
            "query_builder" => function (EntityRepository $er) use ($security) {
                $qb =  $er->createQueryBuilder("g")
                    ->select("g");

                $user = $security->getUser();
                assert($user instanceof User);

                if (!$security->isGranted("ROLE_ADMIN")) {
                    if ($user->getGroup()) {
                        $qb = $qb->where("g.id = :group")
                            ->setParameter("group", $user->getGroup()->getId(), "ulid");
                    } else {
                        $qb = $qb->where("g.id iS NULL");
                    }
                }

                return $qb
                    ->addOrderBy("g.shortName", "ASC");
            },
            "empty_data" => null,
            "placeholder" => "Select a owner group",
            "multiple" => false,
            "attr"  => [
                "class" => "gin-fancy-select",
                "data-allow-empty" => "true",
            ],
        ]);
    }
}