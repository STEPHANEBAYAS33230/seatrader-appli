<?php

namespace App\Repository;

use App\Entity\FiltreDateMiseEnAvant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FiltreDateMiseEnAvant|null find($id, $lockMode = null, $lockVersion = null)
 * @method FiltreDateMiseEnAvant|null findOneBy(array $criteria, array $orderBy = null)
 * @method FiltreDateMiseEnAvant[]    findAll()
 * @method FiltreDateMiseEnAvant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FiltreDateMiseEnAvantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FiltreDateMiseEnAvant::class);
    }

    // /**
    //  * @return FiltreDateMiseEnAvant[] Returns an array of FiltreDateMiseEnAvant objects
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
    public function findOneBySomeField($value): ?FiltreDateMiseEnAvant
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
