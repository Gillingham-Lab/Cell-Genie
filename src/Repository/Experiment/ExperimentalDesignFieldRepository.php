<?php
declare(strict_types=1);

namespace App\Repository\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesignField;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExperimentalDesignField|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExperimentalDesignField|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExperimentalDesignField[]    findAll()
 * @method ExperimentalDesignField[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExperimentalDesignFieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExperimentalDesignField::class);
    }
}