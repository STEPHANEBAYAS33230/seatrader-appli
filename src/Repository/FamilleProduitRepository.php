<?php

namespace App\Repository;

use App\Entity\FamilleProduit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FamilleProduit|null find($id, $lockMode = null, $lockVersion = null)
 * @method FamilleProduit|null findOneBy(array $criteria, array $orderBy = null)
 * @method FamilleProduit[]    findAll()
 * @method FamilleProduit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FamilleProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FamilleProduit::class);
    }

    // /**
    //  * @return FamilleProduit[] Returns an array of FamilleProduit objects
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
    public function findOneBySomeField($value): ?FamilleProduit
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
