<?php

namespace App\Repository;

use App\Entity\FiltreSociete;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Utilisateur|null find($id, $lockMode = null, $lockVersion = null)
 * @method Utilisateur|null findOneBy(array $criteria, array $orderBy = null)
 * @method Utilisateur[]    findAll()
 * @method Utilisateur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UtilisateurRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof Utilisateur) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function filtrerSociete(FiltreSociete $filtre) {
        if ($filtre!=null){
        $search=$filtre->getNom();}
        if (empty($search)) { $search="";}


        $result = $this->createQueryBuilder('u')
            ->where('locate(:nom, u.nomDeLaSociete)!=0 ') // recherche de nom ds nomdelasociete ('locate(:search, s.nom)!=0 ')
            ->setParameter('nom', $search)
            ->getQuery()
            ->getResult();


        return $result;
    }

    public function trouverUtilisateur($nomDLS)
    {   $roles='ROLE_USER';
        return $this->createQueryBuilder('u')
            ->Where('u.nomDeLaSociete = :val')
            ->setParameter('val', $nomDLS)
            ->andWhere('locate(:roles, u.roles)!=0 ')
            //->andWhere('u.roles = :roles')
            ->setParameter('roles', $roles)
            ->getQuery()
            ->getResult();

    }

    public function trouverAdminis($nomDLS)
    {   $roles='ROLE_ADMIN';
        return $this->createQueryBuilder('u')
            ->Where('u.nomDeLaSociete = :val')
            ->setParameter('val', $nomDLS)
            ->andWhere('locate(:roles, u.roles)!=0 ')
            //->andWhere('u.roles = :roles')
            ->setParameter('roles', $roles)
            ->getQuery()
            ->getResult();

    }
    // /**
    //  * @return Utilisateur[] Returns an array of Utilisateur objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Utilisateur
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
