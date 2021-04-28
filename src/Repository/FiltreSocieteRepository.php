<?php

namespace App\Repository;

use App\Entity\FiltreSociete;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FiltreSociete|null find($id, $lockMode = null, $lockVersion = null)
 * @method FiltreSociete|null findOneBy(array $criteria, array $orderBy = null)
 * @method FiltreSociete[]    findAll()
 * @method FiltreSociete[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FiltreSocieteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FiltreSociete::class);
    }

    // /**
    //  * @return FiltreSociete[] Returns an array of FiltreSociete objects
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
    public function findOneBySomeField($value): ?FiltreSociete
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
