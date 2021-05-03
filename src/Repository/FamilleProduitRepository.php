<?php

namespace App\Repository;

use App\Entity\FamilleProduit;
use App\Entity\FiltreFamilleProduit;
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
    public function filtrerFamille(FiltreFamilleProduit $filtre) {
        $search=$filtre->getNom();
        $rien="";
        if (empty($search)) { $search="";}


        $result = $this->createQueryBuilder('f')
            ->where('f.nomFamille = :nomf or :nomf = :rien') // filtre famille identique
            ->setParameter('nomf', $search)
            ->setParameter('rien', $rien)
            ->getQuery()
            ->getResult();


        return $result;
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
