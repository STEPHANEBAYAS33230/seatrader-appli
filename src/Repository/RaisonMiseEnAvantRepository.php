<?php

namespace App\Repository;

use App\Entity\RaisonMiseEnAvant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RaisonMiseEnAvant|null find($id, $lockMode = null, $lockVersion = null)
 * @method RaisonMiseEnAvant|null findOneBy(array $criteria, array $orderBy = null)
 * @method RaisonMiseEnAvant[]    findAll()
 * @method RaisonMiseEnAvant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RaisonMiseEnAvantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RaisonMiseEnAvant::class);
    }

    // /**
    //  * @return RaisonMiseEnAvant[] Returns an array of RaisonMiseEnAvant objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RaisonMiseEnAvant
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
