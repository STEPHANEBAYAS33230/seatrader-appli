<?php

namespace App\Repository;

use App\Entity\Commande;
use App\Entity\Utilisateur;
use DateInterval;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Commande|null find($id, $lockMode = null, $lockVersion = null)
 * @method Commande|null findOneBy(array $criteria, array $orderBy = null)
 * @method Commande[]    findAll()
 * @method Commande[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }


    public function filtrerLesCdes(Utilisateur $user, $today)
    {
       // $today->add(new DateInterval('P1D'));
        $today=date('YYYY-mm-dd');
        $idi=$user->getId();


        $result = $this->createQueryBuilder('c')
            ->where('c.jourDeLivraison >= : today') // gestion date
            ->setParameter('today', $today)
            ->andWhere('c.utilisateur.id = :idi') //gsestion user
            ->setParameter('idi', $idi)
            ->getQuery()
            ->getResult();
        return $result;
    }
    // /**
    //  * @return Commande[] Returns an array of Commande objects
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
    public function findOneBySomeField($value): ?Commande
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
