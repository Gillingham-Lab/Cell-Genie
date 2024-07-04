<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\ExperimentalRun;
use App\Entity\ExperimentalRunWell;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExperimentalRunWell|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExperimentalRunWell|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExperimentalRunWell[]    findAll()
 * @method ExperimentalRunWell[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExperimentalRunWellRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExperimentalRunWell::class);
    }

    public function getByExperimentalRun(ExperimentalRun $experiment)
    {
        return $this->createQueryBuilder("erw")
            ->where("erw.experimentalRun = :experimentalRun")
            ->orderBy("erw.wellNumber", "ASC")
            ->getQuery()
            ->getResult();
    }
}
