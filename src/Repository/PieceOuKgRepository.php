<?php

namespace App\Repository;

use App\Entity\PieceOuKg;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PieceOuKg|null find($id, $lockMode = null, $lockVersion = null)
 * @method PieceOuKg|null findOneBy(array $criteria, array $orderBy = null)
 * @method PieceOuKg[]    findAll()
 * @method PieceOuKg[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PieceOuKgRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PieceOuKg::class);
    }

    // /**
    //  * @return PieceOuKg[] Returns an array of PieceOuKg objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PieceOuKg
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
