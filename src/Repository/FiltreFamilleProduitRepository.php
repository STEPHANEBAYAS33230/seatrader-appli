<?php

namespace App\Repository;

use App\Entity\FiltreFamilleProduit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FiltreFamilleProduit|null find($id, $lockMode = null, $lockVersion = null)
 * @method FiltreFamilleProduit|null findOneBy(array $criteria, array $orderBy = null)
 * @method FiltreFamilleProduit[]    findAll()
 * @method FiltreFamilleProduit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FiltreFamilleProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FiltreFamilleProduit::class);
    }




    // /**
    //  * @return FiltreFamilleProduit[] Returns an array of FiltreFamilleProduit objects
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
    public function findOneBySomeField($value): ?FiltreFamilleProduit
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
