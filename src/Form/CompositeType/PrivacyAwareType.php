<?php
declare(strict_types=1);

namespace App\Form\CompositeType;

use App\Entity\DoctrineEntity\User\User;
use App\Entity\DoctrineEntity\User\UserGroup;
use App\Entity\Interface\PrivacyAwareInterface;
use App\Form\BasicType\FancyEntityType;
use App\Form\BasicType\FormGroupType;
use App\Genie\Enums\PrivacyLevel;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<PrivacyAwareInterface>
 */
class PrivacyAwareType extends AbstractType
{
    public function getParent(): string
    {
        return FormGroupType::class;
    }

    public function __construct(
        private Security $security,
    ) {}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "icon" => "privacy",
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $security = $this->security;

        $builder
            ->add("owner", FancyEntityType::class, [
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
                        } elseif ($user) {
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
                "allow_empty" => true,
            ])
            ->add("group", FancyEntityType::class, [
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
                        } elseif ($user) {
                            $qb = $qb->where("g.id iS NULL");
                        }
                    }

                    return $qb
                        ->addOrderBy("g.shortName", "ASC");
                },
                "empty_data" => null,
                "placeholder" => "Select a owner group",
                "multiple" => false,
                "allow_empty" => true,
            ])
            ->add("privacyLevel", EnumType::class, [
                "label" => "Privacy level",
                "help" => "Public entries are visible for everyone, but only group members and admins can edit them. Group entries restrict the visibility to the group. Private entries restrict the visibility (and thus, the ability to edit) to the owner.",
                "class" => PrivacyLevel::class,
                "disabled" => !($security->isGranted("ROLE_ADMIN") or $security->isGranted("owns", $builder->getData())),
                "empty_data" => 1,
            ])
        ;
    }
}
