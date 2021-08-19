<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\FileBlob;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FileBlob|null find($id, $lockMode = null, $lockVersion = null)
 * @method FileBlob|null findOneBy(array $criteria, array $orderBy = null)
 * @method FileBlob[]    findAll()
 * @method FileBlob[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileBlobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FileBlob::class);
    }

    // /**
    //  * @return FileBlob[] Returns an array of FileBlob objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FileBlob
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
