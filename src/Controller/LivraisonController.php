<?php

namespace App\Controller;

use App\Entity\CalendrierLivraison;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LivraisonController extends AbstractController
{
    /**
     * @Route("/livraison", name="livraison")
     */
    public function index( EntityManagerInterface $em): Response
    {
        //*******recup datejour et user
        $user=$this->getUser();
        $today = new \DateTime('now');
        // *****
        $calendrierLivRepo = $this->getDoctrine()->getRepository(CalendrierLivraison::class);
        $oldCalendrierLiv=$calendrierLivRepo->filtreOldDate($today);
        foreach ($oldCalendrierLiv as $cal){
        $em->remove($cal);}
        $em->flush();
        // recupere toutes les date de calendrierL
        $calendrierLiv = $calendrierLivRepo->findAll();
        $dateArray=[];
        $jourArray=[];

        $dateStart = new \DateTime('now');
        $joursem = array('dim', 'lun', 'mar', 'mer', 'jeu', 'ven', 'sam');
        for ($i = 1; $i <= 62; $i++) {
            $dateStart->add(new DateInterval('P1D'));
            //array_push( $dateArray, $dateStart);
            $dateArray[$i]=(date_format($dateStart,"d-m-Y"));
            //********connaitre le jour de la semaine
            // extraction des jour, mois, an de la date
            $dtt="";
            $dtt=date_format($dateStart,"d-m-Y");
            list($jour, $mois, $annee) = explode('-', $dtt);
            // calcul du timestamp
            $timestamp = mktime (0, 0, 0, $mois, $jour, $annee);
            // affichage du jour de la semaine
            $jourArray[$i]= $joursem[date("w",$timestamp)];
            //$jourArray[$i]=$joursem;
            //array_push( $jourArray, $joursem);
        }
        return $this->render('livraison/index.html.twig', [
            "dateToday"=>$today,"user"=>$user, 'dateArray'=>$dateArray, 'jourArray'=>$jourArray,'calendrierLiv'=>$calendrierLiv,
        ]);
    }

    //********************bloquer une nouvelle date
    /**
     * @Route("/livraison/{dtt}", name="bloquer_date")
     */
    public function bloquerdate($dtt, EntityManagerInterface $em){
        //$cdeRepo = $this->getDoctrine()->getRepository(CalendrierLivraison::class);
        $newCalendrierLiv = new CalendrierLivraison();
        $newCalendrierLiv->setDateLivraison(date_create_from_format("d-m-Y",$dtt));
        $newCalendrierLiv->setOuverteBloque("Bloquée");
        //********************
        $em->persist($newCalendrierLiv);
        $em->flush();
        return $this->redirectToRoute('livraison');
    }


    //********************bloquer une nouvelle date
    //********************debloquer une date
    /**
     * @Route("/livraison/ouvrir/{id}", name="debloquer_date")
     */
    public function debloquerdate($id, EntityManagerInterface $em){
        // recupere la date
        $calendrierRepo = $this->getDoctrine()->getRepository(CalendrierLivraison::class);
        $newCalendrierLiv = $calendrierRepo->find($id);
        $em->remove($newCalendrierLiv);
        $em->flush();
        return $this->redirectToRoute('livraison');
    }


    //********************debloquer une date
//********************bloquer une nouvelle date
    /**
     * @Route("/livraison/blocquer/{dtt}", name="debloquer_wk")
     */
    public function debloquer_wk($dtt, EntityManagerInterface $em){
        //$cdeRepo = $this->getDoctrine()->getRepository(CalendrierLivraison::class);
        $newCalendrierLiv = new CalendrierLivraison();
        $newCalendrierLiv->setDateLivraison(date_create_from_format("d-m-Y",$dtt));
        $newCalendrierLiv->setOuverteBloque("Ouverte");
        //********************
        $em->persist($newCalendrierLiv);
        $em->flush();
        return $this->redirectToRoute('livraison');
    }
}
