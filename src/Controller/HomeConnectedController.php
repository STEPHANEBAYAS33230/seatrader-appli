<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\EtatCommande;
use App\Entity\MiseEnAvant;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeConnectedController extends AbstractController
{
    /**
     * @Route("/home/connected", name="home_connected")
     */
    public function index(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        //maintenance effacer les miseenavant de plus d'un mois
        // récupère repository
        $dtplus = new \DateTime('now');
        $dtmoins= new \DateTime('now');
        $dtmoins->sub(new DateInterval('P365D'));
        $dtplus->sub(new DateInterval('P30D'));
        $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
        $miseEnAvant = $miseEnAvantRepo->filtrer($dtplus, $dtmoins);
        //********************on boucle les miseEnavant à effacer
        foreach( $miseEnAvant as $mea ) {
            $em->remove($mea);}
        $em->flush();
        //**************************************************
        //maintenance passer les cde recues et traitées de+ d'un mois en archive
        // récupère repository
        // recupere l etat envoyee
        $etatRepo = $this->getDoctrine()->getRepository(EtatCommande::class);
        $etat = $etatRepo->find(4);
        $cdeRepo = $this->getDoctrine()->getRepository(Commande::class);
        $cde = $cdeRepo->passerArchive($etat);
        //********************on boucle les cde et on les change en etat archivee
        $etatRepo = $this->getDoctrine()->getRepository(EtatCommande::class);
        $etat = $etatRepo->find(1); //on recupere etat archivée
        foreach( $cde as $value ) {
            $value->setEtatCommande($etat);
               $em->persist($value);
        }
        $em->flush();
        //**************************************************
        //**************************************************
        //maintenance passer les cde archivees de+ de 2 mois à effacer
        // récupère repository
        // recupere l etat envoyee
        $etatRepo = $this->getDoctrine()->getRepository(EtatCommande::class);
        $etat = $etatRepo->find(1); //on recuper l etat archivee
        $cdeRepo = $this->getDoctrine()->getRepository(Commande::class);
        $cde = $cdeRepo->effacerArchive($etat);

        foreach( $cde as $value ) {
            $em->remove($value);
        }
        $em->flush();
        //**************************************************


        //***************************************
        // on récupère l'user
        $user=$this->getUser();
        $role=$user->getRoles();

        $role=$user->getRoles();
        if ($role==['ROLE_USER']){
            if ($user->getEtatUtilisateur()=="INACTIF"){
                $this->addFlash('error', "Compte désactivé (veuillez contacter l'administrateur)");
                return $this->redirectToRoute('app_logout');
            }
            return $this->redirectToRoute('home_connected_user');
        }


        $today = strftime('%A %d %B %Y %I:%M:%S');
        // si l'utilisateur est admin VVVVVVV (route si dessous)
        //******recuperer les cdes envoyée
        //****************on recupere la cde
        $cdeRepo = $this->getDoctrine()->getRepository(Commande::class);
        $commandesEnvoyees = $cdeRepo->filtreCdeStatut(2);
        $commandesReceptionnee = $cdeRepo->filtreCdeStatut(3);
        $commandesTraitee = $cdeRepo->filtreCdeStatut(4);
        $commandesArchivee = $cdeRepo->filtreCdeStatut(1);

        return $this->render('home_connected/homeAdmin.html.twig', [
            'dateToday'=>$today,"user"=>$user, 'commandesEnvoyees'=>$commandesEnvoyees, 'commandesReceptionnee'=>$commandesReceptionnee,
            'commandesTraitee'=>$commandesTraitee, 'commandesArchivee'=>$commandesArchivee,
        ]);
    }
}
