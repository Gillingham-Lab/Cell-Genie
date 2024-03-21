<?php
declare(strict_types=1);

namespace App\Form\Search;

use App\Entity\DoctrineEntity\User\UserGroup;
use App\Entity\Organism;
use App\Entity\Tissue;
use App\Repository\Substance\UserGroupRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class CellSearchType extends AbstractType
{
    public function __construct(
        private UserGroupRepository $userGroupRepository,
    ) {

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("cellNumber", TextType::class, [
                "label" => "Cell number",
                "required" => false,
            ])
            ->add("cellIdentifier", TextType::class, [
                "label" => "Cell RRID or cellosaurus number",
                "help" => "This will search the database for a match in either of these cells",
                "required" => false,
            ])
            ->add("cellName", TextType::class, [
                "label" => "Cell name",
                "required" => false,
            ])
            ->add("cellGroupName", TextType::class, [
                "label" => "Cell group name",
                "required" => false,
            ])
            /*->add("groupOwner", ChoiceType::class, [
                "label" => "Group",
                "choices" => [null, ...$this->userGroupRepository->findAll()],
                "choice_label" => fn(?UserGroup $userGroup) => $userGroup?->getShortName() ?? "Any",
                "choice_value" => fn(?UserGroup $userGroup) => $userGroup?->getId()->toRfc4122() ?? null,
            ])*/
            ->add("groupOwner", EntityType::class, [
                "label" => "Group",
                "class" => UserGroup::class,
                "required" => false,
                "choice_value" => function (UserGroup|null|string $entity) {
                    if (is_string($entity)) {
                        return $entity;
                    } else {
                        return $entity?->getId()?->toRfc4122();
                    }
                }
            ])
            ->add("isCancer", ChoiceType::class, [
                "label" => "Is a cancer cell line",
                "choices" => [
                    "Yes" => "true",
                    "No" => "false",
                ],
                "empty_data" => null,
                "required" => false,
            ])
            ->add("isEngineered", ChoiceType::class, [
                "label" => "Is an engineered cell line",
                "choices" => [
                    "Yes" => "true",
                    "No" => "false",
                ],
                "empty_data" => null,
                "required" => false,
            ])
            ->add("organism", EntityType::class, [
                "label" => "Organism",
                "class" => Organism::class,
                "required" => false,
                "choice_value" =>function (Organism|null|string $entity) {
                    if (is_string($entity)) {
                        return $entity;
                    } else {
                        return $entity?->getId();
                    }
                }
            ])
            ->add("tissue", EntityType::class, [
                "label" => "Tissue",
                "class" => Tissue::class,
                "required" => false,
                "choice_value" =>function (Tissue|null|string $entity) {
                    if (is_string($entity)) {
                        return $entity;
                    } else {
                        return $entity?->getId();
                    }
                }
            ])
        ;
    }
}