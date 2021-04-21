<?php

namespace App\Repository;

use App\Entity\Colisage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Colisage|null find($id, $lockMode = null, $lockVersion = null)
 * @method Colisage|null findOneBy(array $criteria, array $orderBy = null)
 * @method Colisage[]    findAll()
 * @method Colisage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ColisageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Colisage::class);
    }

    // /**
    //  * @return Colisage[] Returns an array of Colisage objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Colisage
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
