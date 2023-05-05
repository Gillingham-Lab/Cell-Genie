<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\DoctrineEntity\User\User;
use App\Entity\Experiment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Experiment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Experiment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Experiment[]    findAll()
 * @method Experiment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExperimentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Experiment::class);
    }

    public function findByOwner(User $owner)
    {
        return $this->createQueryBuilder("e")
            ->andWhere("e.owner = :owner")
            ->setParameter("owner", $owner)
            ->orderBy("e.modifiedAt", "DESC")
            ->addOrderBy("e.createdAt", "DESC")
            ->getQuery()
            ->getResult()
        ;
    }

    public function findNotByOwner(User $owner)
    {
        return $this->createQueryBuilder("e")
            ->andWhere("e.owner != :owner")
            ->setParameter("owner", $owner)
            ->orderBy("e.modifiedAt", "DESC")
            ->addOrderBy("e.createdAt", "DESC")
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Experiment[] Returns an array of Experiment objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Experiment
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
