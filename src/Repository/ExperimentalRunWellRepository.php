<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\ExperimentalRun;
use App\Entity\ExperimentalRunWell;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExperimentalRunWell>
 */
class ExperimentalRunWellRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExperimentalRunWell::class);
    }

    /**
     * @return ExperimentalRunWell[]
     */
    public function getByExperimentalRun(ExperimentalRun $experiment): array
    {
        return $this->createQueryBuilder("erw")
            ->where("erw.experimentalRun = :experimentalRun")
            ->orderBy("erw.wellNumber", "ASC")
            ->getQuery()
            ->getResult();
    }
}
