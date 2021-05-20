<?php

namespace App\Repository;

use App\Entity\CalendrierLivraison;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CalendrierLivraison|null find($id, $lockMode = null, $lockVersion = null)
 * @method CalendrierLivraison|null findOneBy(array $criteria, array $orderBy = null)
 * @method CalendrierLivraison[]    findAll()
 * @method CalendrierLivraison[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CalendrierLivraisonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CalendrierLivraison::class);
    }

    //******retourner les anciennes dates bloquées ou ouvertes pour les effacer
    public function filtreOldDate($date){
        return $this->createQueryBuilder('c')
            ->where('c.dateLivraison < :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult()
        ;
    }

    public function filtreDateBloc($date){
        $bloque="Bloquée";
        return $this->createQueryBuilder('c')
            ->where('c.dateLivraison = :date')
            ->setParameter('date', $date)
            ->andWhere('c.OuverteBloque = :bloque')
            ->setParameter('bloque', $bloque)
            ->getQuery()
            ->getResult()
            ;
    }

    public function filtreDateOpen($date){
        $ouvert="Ouverte";
        return $this->createQueryBuilder('c')
            ->where('c.dateLivraison = :date')
            ->setParameter('date', $date)
            ->andWhere('c.OuverteBloque = :ouvert')
            ->setParameter('ouvert', $ouvert)
            ->getQuery()
            ->getResult()
            ;
    }
    // /**
    //  * @return CalendrierLivraison[] Returns an array of CalendrierLivraison objects
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
    public function findOneBySomeField($value): ?CalendrierLivraison
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
