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


    public function filtrerLesCdes(Utilisateur $user)
    {   //pour les users simple
       // $today->add(new DateInterval('P1D'));
        $today = new \DateTime('now');
        //$today->add(new DateInterval('P1D'));
        //$idi=$user->getId();


        $result = $this->createQueryBuilder('c')
            ->where('c.jourDeLivraison > :today') // gestion date
            ->setParameter('today', $today)
            ->andWhere('c.utilisateur = :user') //gsestion user
           ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
        return $result;
    }

    //***********filtrer les anciennes cdes -1mois en arriere max(<datejour non modifiable et non supprimable)

    public function filtrerLesAnciennesCdes(Utilisateur $user)
    {   //pour les users simple
        // $today->add(new DateInterval('P1D'));
        $today = new \DateTime('now');
        //$today->add(new DateInterval('P1D'));
        //$idi=$user->getId();
        $dateLimiteAncienne=new \DateTime('now');
        $dateLimiteAncienne->sub(new DateInterval('P31D'));
        $result = $this->createQueryBuilder('c')
            ->where('c.jourDeLivraison <= :today') // gestion date
            ->setParameter('today', $today)
            ->andWhere('c.jourDeLivraison > :dla') // gestion date
            ->setParameter('dla', $dateLimiteAncienne)
            ->andWhere('c.utilisateur = :user') //gsestion user
            ->setParameter('user', $user)
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
