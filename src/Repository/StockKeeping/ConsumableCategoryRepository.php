<?php
declare(strict_types=1);

namespace App\Repository\StockKeeping;

use App\Entity\DoctrineEntity\StockManagement\ConsumableCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConsumableCategory>
 */
class ConsumableCategoryRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private EntityManagerInterface $em,
    ) {
        parent::__construct($registry, ConsumableCategory::class);
    }

    /**
     * @param $id
     * @param $lockMode
     * @param $lockVersion
     * @return ?ConsumableCategory
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function find($id, $lockMode = null, $lockVersion = null): ?object
    {
        return $this->createQueryBuilder("cc")
            ->addSelect("c")
            ->leftJoin("cc.consumables", "c")
            ->where("cc.id = :id")
            ->groupBy("cc")
            ->addGroupBy("c")
            ->setParameter("id", $id, "ulid")
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @return ConsumableCategory[]
     */
    public function findAllWithConsumablesAndLots(): array {
        $result = $this->createQueryBuilder("cc")
            ->addSelect("c")
            ->addSelect("cl")
            ->indexBy("cc", "cc.id")
            ->leftJoin("cc.consumables", "c")
            ->leftJoin("c.lots", "cl")
            ->groupBy("cc")
            ->addGroupBy("c")
            ->addGroupBy("cl")
            ->getQuery()->getResult();

        return $this->setUpRelations($result);
    }

    /**
     * @param ConsumableCategory[] $categories
     * @return ConsumableCategory[]
     */
    private function setUpRelations(array $categories): array {
        $metadata = $this->em->getClassMetadata(ConsumableCategory::class);
        $idField = $metadata->reflFields["id"];
        $parentField = $metadata->reflFields["parent"];
        #$parentIdField = $metadata->reflFields["parentId"];
        $childrenField = $metadata->reflFields["children"];

        foreach ($categories as $category) {
            $children = $childrenField->getValue($category);
            $children->setInitialized(true);

            $parent = $categories[$parentField->getValue($category)?->getId()->toRfc4122()] ?? null;

            if ($parent === null) {
                continue;
            }

            $children = $childrenField->getValue($parent);

            if (!$children->contains($category)) {
                $parentField->setValue($category, $parent);
                $children->add($category);
            }
        }

        return array_values($categories);
    }
}