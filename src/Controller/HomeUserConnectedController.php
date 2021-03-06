<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Entity\FiltreDateMiseEnAvant;
use App\Entity\MiseEnAvant;
use App\Entity\Utilisateur;
use App\Form\FiltreDateMiseEnAvantType;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route ("/monAppli")
 */
class HomeUserConnectedController extends AbstractController
{
    /**
     * @Route("/home/user-connected", name="home_connected_user")
     */
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $dateChoisie=null;
        $today = new \DateTime('now');
        $dtplus = new \DateTime('now');
        $dtmoins= new \DateTime('now');
        $dtmoins->sub(new DateInterval('P1D'));
        $dtplus->add(new DateInterval('P1D'));
        $dtplus->format('Y-m-d');
        $dtmoins->format('Y-m-d');
        $today->format('Y-m-d');
        // on récupère l'user
        $user=$this->getUser();
        //**************ON RECUPERE LE PDF
        //****************on recupere le produit
        try {
            $coursRepo = $this->getDoctrine()->getRepository(Cours::class);
            $lecours = $coursRepo->findAll();
        } catch (\Doctrine\DBAL\Exception $e) {
            $errorMessage = $e->getMessage();
            $this->addFlash('error', 'Problème d\'accès à la base de données: ' . 'homeUserConnectedController44');
            return $this->redirectToRoute('app_logout');
        }
        $cours=$lecours[0];
        //******creation du formulaire filtreMiseEnAvant
        //*********creation du formulaire
        $filtreDateMiseEnAvant= new FiltreDateMiseEnAvant();
        $filtreDateMiseEnAvant->setDatePlus($dtplus);
        $filtreDateMiseEnAvant->setDateMeA($dtmoins);
        $filtreDateMiseEnAvantForm = $this->createForm(FiltreDateMiseEnAvantType::class, $filtreDateMiseEnAvant);
        $filtreDateMiseEnAvantForm->handleRequest($request);
        //**********si formulaire date valider
        if ($filtreDateMiseEnAvantForm->isSubmitted() and $filtreDateMiseEnAvantForm->isValid() )
        {
            try {
                $coursRepo = $this->getDoctrine()->getRepository(Cours::class);
                $lecours = $coursRepo->findAll();
            } catch (\Doctrine\DBAL\Exception $e) {
                $errorMessage = $e->getMessage();
                $this->addFlash('error', 'Problème d\'accès à la base de données: ' . 'homeUserConnectedController63');
                return $this->redirectToRoute('app_logout');
            }
            $cours=$lecours[0];
            $dtplus=$filtreDateMiseEnAvant->getDatePlus();
            $dtmoins=$filtreDateMiseEnAvant->getDateMeA();
            try {
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $miseEnAvant = $miseEnAvantRepo->filtrer($dtplus, $dtmoins);
            } catch (\Doctrine\DBAL\Exception $e) {
                $errorMessage = $e->getMessage();
                $this->addFlash('error', 'Problème d\'accès à la base de données: ' . 'homeUserConnectedController74');
                return $this->redirectToRoute('app_logout');
            }
            $today = new \DateTime('now');
            return $this->render('home_user_connected/index.html.twig', ["dateToday"=>$today, "user"=>$user,"miseEnAvant" => $miseEnAvant,
                "filtreDateMiseEnAvantForm"=>$filtreDateMiseEnAvantForm->createView(),'cours'=>$cours,
            ]);





            }

            //*******recup mise en avant page home à afficher
        for($i=1;$i<31;$i++) {
            $today->add(new DateInterval('P1D'));
            $dtmoins->add(new DateInterval('P1D'));
            $dtplus->add(new DateInterval('P1D'));

            // récupère repository
            try {
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $miseEnAvant = $miseEnAvantRepo->filtrer($dtplus, $dtmoins);
            } catch (\Doctrine\DBAL\Exception $e) {
                $errorMessage = $e->getMessage();
                $this->addFlash('error', 'Problème d\'accès à la base de données: ' .'homeUserConnectedController100');
                return $this->redirectToRoute('app_logout');
            }
            if (!empty($miseEnAvant)){
                break;
            }

        }
        //**************************si miseEnavant vide ds le futur (1mois)-->passé à moins 30j
        if (empty($miseEnAvant)){
            $today = new \DateTime('now');
            $dtplus = new \DateTime('now');
            $dtmoins= new \DateTime('now');
            $dtmoins->sub(new DateInterval('P1D'));
            $dtplus->add(new DateInterval('P1D'));
            $dtplus->format('Y-m-d');
            $dtmoins->format('Y-m-d');
            $today->format('Y-m-d');
            //****************mise ds le passé
            for($i=1;$i<31;$i++) {
                $today->sub(new DateInterval('P1D'));
                $dtmoins->sub(new DateInterval('P1D'));
                $dtplus->sub(new DateInterval('P1D'));

                // récupère repository
                try {
                    $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                    $miseEnAvant = $miseEnAvantRepo->filtrer($dtplus, $dtmoins);
                } catch (\Doctrine\DBAL\Exception $e) {
                    $errorMessage = $e->getMessage();
                    $this->addFlash('error', 'Problème d\'accès à la base de données: ' .'homeUserConnectedController130');
                    return $this->redirectToRoute('app_logout');
                }
                if (!empty($miseEnAvant)){
                    break;
                }

            }
            //***********fin de IF
        }// fin recup mise en avant*********************************
        $today = new \DateTime('now');
        $today->format('Y-m-d');
        return $this->render('home_user_connected/index.html.twig', ["dateToday"=>$today, "user"=>$user,"miseEnAvant" => $miseEnAvant,
            "filtreDateMiseEnAvantForm"=>$filtreDateMiseEnAvantForm->createView(), 'cours'=>$cours
        ]);
    }
}
