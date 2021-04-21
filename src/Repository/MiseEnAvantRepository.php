<?php

namespace App\Repository;

use App\Entity\MiseEnAvant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MiseEnAvant|null find($id, $lockMode = null, $lockVersion = null)
 * @method MiseEnAvant|null findOneBy(array $criteria, array $orderBy = null)
 * @method MiseEnAvant[]    findAll()
 * @method MiseEnAvant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MiseEnAvantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MiseEnAvant::class);
    }

    // /**
    //  * @return MiseEnAvant[] Returns an array of MiseEnAvant objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MiseEnAvant
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
