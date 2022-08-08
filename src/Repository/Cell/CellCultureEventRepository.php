<?php

namespace App\Repository\Cell;

use App\Entity\DoctrineEntity\Cell\CellCultureEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CellCultureEvent>
 *
 * @method CellCultureEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method CellCultureEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method CellCultureEvent[]    findAll()
 * @method CellCultureEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CellCultureEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CellCultureEvent::class);
    }

    public function add(CellCultureEvent $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CellCultureEvent $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return CellCultureEvent[] Returns an array of CellCultureEvent objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CellCultureEvent
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
