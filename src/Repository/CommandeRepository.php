<?php

namespace App\Repository;

use App\Entity\Commande;
use App\Entity\EtatCommande;
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

    //filter cde avec date livraison apres date du jour -l'utilisateur a encore du temps (au moins 24h) sur FOND VERT
    public function filtrerLesCdes(Utilisateur $user)
    {
        $today = new \DateTime('now');
        $today->add(new DateInterval('P1D'));
        //$idi=$user->getId();


        $result = $this->createQueryBuilder('c')
            ->where('c.jourDeLivraison > :today') // gestion date
            ->setParameter('today', $today)
            ->andWhere('c.utilisateur = :user') //gsestion user
           ->setParameter('user', $user)
            ->orderBy('c.jourDeLivraison', 'DESC')
            ->getQuery()
            ->getResult();
        return $result;
    }

    //***********filtrer les anciennes cdes -1mois en arriere max(<datejour non modifiable et non supprimable)
    //FILTRER VIEILLE CDE  recuperer les cdee avant la date du jour et -30jours D'ANCIENNETE FOND GRIS
    public function filtrerLesAnciennesCdes(Utilisateur $user)
    {
        $today = new \DateTime('now');
        $dateLimiteAncienne=new \DateTime('now');
        $dateLimiteAncienne->sub(new DateInterval('P31D'));
        $result = $this->createQueryBuilder('c')
            ->where('c.jourDeLivraison <= :today') // gestion date
            ->setParameter('today', $today)
            ->andWhere('c.jourDeLivraison > :dla') // gestion date
            ->setParameter('dla', $dateLimiteAncienne)
            ->andWhere('c.utilisateur = :user') //gsestion user
            ->setParameter('user', $user)
            ->orderBy('c.jourDeLivraison', 'DESC')
            ->getQuery()
            ->getResult();
        return $result;
    }
    // FILTRER recupere les commandes +1j ou apres date du jour modifiable encore jusqu a 11h du matin en  FOND ORANGE
    public function filtrerLesCdesEgalToday(Utilisateur $user)
    {   //pour les users simple ceux qui st encore modifiable et supprimable
        $today = new \DateTime('now');
        //$today->add(new DateInterval('P1D'));
        $today2 = new \DateTime('now');
        $today2->add(new DateInterval('P2D'));
        $heure=intval(Date( 'H'));
        $onze=11;


        $result = $this->createQueryBuilder('c')
            ->where('c.jourDeLivraison > :today') // gestion date
            ->setParameter('today', $today)
            ->andWhere('c.jourDeLivraison < :todayw') // gestion date
            ->setParameter('todayw', $today2)
            ->andWhere(':heure < :onze')
            ->setParameter('onze', $onze)
            ->setParameter('heure', $heure)
            ->andWhere('c.utilisateur = :user') //gsestion user
            ->setParameter('user', $user)
            ->orderBy('c.jourDeLivraison', 'DESC')
            ->getQuery()
            ->getResult();
        return $result;
    }
// recupere les commandes +1j apres date du jour non modifiable car heure>11H afficher en FOND GRIS
    public function filtrerLesCdesNonEgalToday(Utilisateur $user)
    {   //pour les users simple ceux qui st encore modifiable et supprimable
        $today = new \DateTime('now');
        //$today->add(new DateInterval('P1D'));
        $today2 = new \DateTime('now');
        $today2->add(new DateInterval('P1D'));
        $heure=intval(Date( 'H'));
        //$idi=$user->getId();


        $result = $this->createQueryBuilder('c')
            ->where('c.jourDeLivraison > :today') // gestion date
            ->setParameter('today', $today)
            ->andWhere('c.jourDeLivraison < :todayw') // gestion date
            ->setParameter('todayw', $today2)
            ->andWhere(':heure > 10')
            ->setParameter('heure', $heure)
            ->andWhere('c.utilisateur = :user') //gsestion user
            ->setParameter('user', $user)
            ->orderBy('c.jourDeLivraison', 'DESC')
            ->getQuery()
            ->getResult();
        return $result;
    }

    public function filtreCdeStatut($etat)
    {
        $result = $this->createQueryBuilder('c')
            ->where('c.etatCommande = :etat') // gestion date
            ->setParameter('etat', $etat)
            ->orderBy('c.jourDeLivraison', 'DESC')
            ->getQuery()
            ->getResult();
        return $result;
    }

    public function passerArchive(EtatCommande $etat)
    {  //*******recuperer les cde traité ancienne d'1mois par rapport à la date du jour
        $today = new \DateTime('now');
        $today->sub(new DateInterval('P30D'));
        $result = $this->createQueryBuilder('c')
            ->where('c.etatCommande = :etat') // gestion etat
            ->setParameter('etat', $etat)
            ->andWhere('c.jourDeLivraison < :today') // gestion date
            ->setParameter('today', $today)
            ->orderBy('c.jourDeLivraison', 'DESC')
            ->getQuery()
            ->getResult();
        return $result;
    }

    public function effacerArchive(EtatCommande $etat)
    {  //*******recuperer les cde archivee d'2mois et + par rapport à la date du jour
        $today = new \DateTime('now');
        $today->sub(new DateInterval('P60D'));
        $result = $this->createQueryBuilder('c')
            ->where('c.etatCommande = :etat') // gestion etat
            ->setParameter('etat', $etat)
            ->andWhere('c.jourDeLivraison < :today') // gestion date
            ->setParameter('today', $today)
            ->orderBy('c.jourDeLivraison', 'DESC')
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
