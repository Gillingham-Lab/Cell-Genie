<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\DoctrineEntity\Resource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Resource|null find($id, $lockMode = null, $lockVersion = null)
 * @method Resource|null findOneBy(array $criteria, array $orderBy = null)
 * @method Resource[]    findAll()
 * @method Resource[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Resource::class);
    }

    public function findCategories($searchTerm = null)
    {
        $qb = $this->createQueryBuilder("r")
            ->distinct(true)
            ->select("r.category")
            ->groupBy("r.category");

        if ($searchTerm) {
            $prefix = "%";
            $suffix = "%";
            if (str_starts_with($searchTerm, "^")) {
                $prefix = "";
                $searchTerm = substr($searchTerm, 1);
            }

            if (str_ends_with($searchTerm, "$")) {
                $suffix = "";
                $searchTerm = substr($searchTerm, 0, -1);
            }

            $searchTerm = $prefix . $searchTerm . $suffix;

            var_dump($searchTerm);

            $qb = $qb
                ->where("r.category LIKE :search")
                ->setParameter("search", $searchTerm);
        }

        $result = $qb->getQuery()->getScalarResult();
        return $result;
    }
}