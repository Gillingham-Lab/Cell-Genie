<?php
declare(strict_types=1);

namespace App\Form\Traits;

use App\Entity\DoctrineEntity\User\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormBuilderInterface;

trait OwnerTrait
{
    private function addOwnerField(FormBuilderInterface $builder, Security $security): void
    {
        $builder->add("owner", EntityType::class, [
            "label" => "Owner",
            "required" => false,
            "class" => User::class,
            "query_builder" => function (EntityRepository $er) use ($security) {
                $qb =  $er->createQueryBuilder("u")
                    ->select("u")
                    ->addSelect("g")
                    ->leftJoin("u.group", "g", Join::ON);

                $user = $security->getUser();
                assert($user instanceof User);

                if (!$security->isGranted("ROLE_ADMIN")) {
                    if ($user->getGroup()) {
                        $qb = $qb->where("u.group = :group")
                            ->setParameter("group", $user->getGroup()->getId(), "ulid");
                    } else {
                        $qb = $qb->where("u.id = :id")
                            ->setParameter("id", $user->getId(), "ulid");
                    }
                }

                $qb = $qb
                    ->addOrderBy("g.shortName", "ASC")
                    ->addOrderBy("u.fullName", "ASC");

                return $qb;
            },
            "group_by" => function (User $user) {
                return $user->getGroup()?->getShortName() ?? "None";
            },
            "empty_data" => null,
            "placeholder" => "Select a owner",
            "multiple" => false,
            "attr"  => [
                "class" => "gin-fancy-select",
                "data-allow-empty" => "true",
            ],
        ]);
    }
}