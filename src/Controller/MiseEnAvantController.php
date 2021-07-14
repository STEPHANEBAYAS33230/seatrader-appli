<?php

namespace App\Controller;

use App\Entity\MiseEnAvant;
use App\Form\MiseEnAvantDeuxType;
use App\Form\MiseEnAvantType;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/**
 * @Route ("/admin")
 */
class MiseEnAvantController extends AbstractController
{
    /**
     * @Route("/mise-en-avant", name="creer_mise_en_avant")
     */
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN'); // acess denied si personne n est pas authentifier ADMIN
        //effacer les mises en avant de +de 1 mois (maintenance)
        $dt1 = new \DateTime('now');
        $dt1->sub(new DateInterval('P31D'));
        $dt2 = new \DateTime('now');
        $dt2->sub(new DateInterval('P365D'));
        //on recupere le repository
        try {
            $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
            // filtre les mea qui sont entre -1mois et 1an
            $oldMise = $miseEnAvantRepo->filtrer($dt2, $dt1);
        } catch (\Doctrine\DBAL\Exception $e) {
            $errorMessage = $e->getMessage();
            $this->addFlash('error', 'Problème d\'accès à la base de données: ' . $errorMessage);
            return $this->redirectToRoute('gérer_mise_en_avant');
        }
        //********************on boucle les miseEnavant à effacer/supprimer
        foreach ($oldMise as $mea) {
            try {
                $em->remove($mea);
                $em->flush();
            } catch (\Doctrine\DBAL\Exception $e) {
                $errorMessage = $e->getMessage();
                $this->addFlash('error', 'Erreur Maintenance Mise En Avant: ' . $errorMessage);
                return $this->redirectToRoute('gérer_mise_en_avant');
            }
        }
        //**************************************************

        // on récupère l'user
        $user = $this->getUser();
        // les dates
        $today = new \DateTime('now');
        $dtplus = new \DateTime('now');
        $dtmoins = new \DateTime('now');
        $dtmoins->sub(new DateInterval('P1D'));
        $dtplus->add(new DateInterval('P1D'));
        $dtplus->format('Y-m-d');
        $dtmoins->format('Y-m-d');
        $today->format('Y-m-d');
        //*******************recherche des mise en avant les +proche de la date du jour à afficher
        for ($i = 1; $i < 31; $i++) {
            $today->add(new DateInterval('P1D'));
            $dtmoins->add(new DateInterval('P1D'));
            $dtplus->add(new DateInterval('P1D'));
            // récupère repository
            try {
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $miseEnAvant = $miseEnAvantRepo->filtrer($dtplus, $dtmoins);
            } catch (\Doctrine\DBAL\Exception $e) {
                $errorMessage = $e->getMessage();
                $this->addFlash('error', 'Problème d\'accès base de données: ' . $errorMessage);
                return $this->redirectToRoute('gérer_mise_en_avant');
            }
            if (!empty($miseEnAvant)) {//dès qu'une mise en avant recente est trouvé on sort de la boucle
                break;
            }

        }
        // si aucune trouvée apres la date du jour, recherche avant date du jour
        //*************nouvelle recherche ds le  passé si miseEnavant vide ds le futur (1mois)-->passé à moins 30j
        if (empty($miseEnAvant)) {
            $today = new \DateTime('now');
            $dtplus = new \DateTime('now');
            $dtmoins = new \DateTime('now');
            $dtmoins->sub(new DateInterval('P1D'));
            $dtplus->add(new DateInterval('P1D'));
            $dtplus->format('Y-m-d');
            $dtmoins->format('Y-m-d');
            $today->format('Y-m-d');
            //****************mise ds le passé
            for ($i = 1; $i < 31; $i++) {
                $today->sub(new DateInterval('P1D'));
                $dtmoins->sub(new DateInterval('P1D'));
                $dtplus->sub(new DateInterval('P1D'));

                // récupère repository
                try {
                    $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                    $miseEnAvant = $miseEnAvantRepo->filtrer($dtplus, $dtmoins);
                } catch (\Doctrine\DBAL\Exception $e) {
                    $errorMessage = $e->getMessage();
                    $this->addFlash('error', 'Problème d\'accès base de données: ' . $errorMessage);
                    return $this->redirectToRoute('gérer_mise_en_avant');
                }

                if (!empty($miseEnAvant)) {
                    break;
                }
            }
        }

        //*****************fin recherche mise en avant****************************************************
        //reinitialisation des dates
        $today = new \DateTime('now');
        $todayPlus = new \DateTime('now');
        $todayPlus->add(new DateInterval('P1D'));
        //**********creation d une nouvelle mise en avant
        $miseEnAvantSelect = new MiseEnAvant();
        // on hydrate la nouvelle mise en avant
        $miseEnAvantSelect->setDateCreation($today);
        $miseEnAvantSelect->setDateLivraisonMiseEnAvant($todayPlus);

        //*********creation du formulaire pour vue 1
        $miseEnAvantForm = $this->createForm(MiseEnAvantType::class, $miseEnAvantSelect);
        $miseEnAvantForm->handleRequest($request);
        $soumission = false;
        //*********creation du deuxieme formulaire pour vue 2
        $miseEnAvantDeuxForm = $this->createForm(MiseEnAvantDeuxType::class, $miseEnAvantSelect);
        $miseEnAvantDeuxForm->handleRequest($request);
        //********************************************************************
        if (($miseEnAvantForm->isSubmitted() and $miseEnAvantForm->isValid())
            or ($miseEnAvantDeuxForm->isSubmitted() and $miseEnAvantDeuxForm->isValid())) {
            // changement d'etat de $soumission comme le premier formulaire validée
            $soumission = true;
            //si $soumission=true la vue2 sera visible/false vue1
            //************
            if ($miseEnAvantDeuxForm->isSubmitted() and $miseEnAvantDeuxForm->isValid()) {// si deuxieme formulaire soumis on enregistre
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                try {
                    $em->persist($miseEnAvantSelect);
                    $em->flush();
                } catch (\Doctrine\DBAL\Exception $e) {
                    $errorMessage = $e->getMessage();
                    $this->addFlash('error', 'Impossible de sauvegarder la mise en avant: ' . $errorMessage);
                    return $this->redirectToRoute('gérer_mise_en_avant');
                }
                return $this->redirectToRoute('creer_mise_en_avant', [

                ]);
            }
            //*********date en cours

            $dtplus = new \DateTime('now');
            $dtmoins = new \DateTime('now');
            $dtmoins->sub(new DateInterval('P1D'));
            $dtplus->add(new DateInterval('P1D'));
            $dtplus->format('Y-m-d');
            $dtmoins->format('Y-m-d');

            $today = new \DateTime('now');
            $todayPlus = new \DateTime('now');
            $todayPlus->add(new DateInterval('P1D'));
            //**********************vers le template deux avec aprecu de la mise en avant
            return $this->render('mise_en_avant/voirMiseEnAvant.html.twig', [
                'miseEnAvantDeuxForm' => $miseEnAvantDeuxForm->createView(), "user" => $user, "soumission" => $soumission,
                "dateToday" => $today, 'miseEnAvantSubmit' => $miseEnAvantSelect, 'miseEnAvant' => $miseEnAvant,
            ]);
        }

        return $this->render('mise_en_avant/creerMiseEnAvant.html.twig', [
            "dateToday" => $today, "user" => $user, "miseEnAvant" => $miseEnAvant, "soumission" => $soumission,
            "miseEnAvantForm" => $miseEnAvantForm->createView(),

        ]);
    }


    //***********gerer les mises en avant
    /**
     * @Route("/gestion-mise-en-avant", name="gérer_mise_en_avant")
     */
    public function gestionMiseEnAvan(EntityManagerInterface $em, Request $request): Response
    {
        //recuperer toutes les mises en avant
        // on récupère l'user
        $user = $this->getUser();
        // recupere toutes les familles
        try {
            $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
        }  catch (\Doctrine\DBAL\Exception $e) {
            $errorMessage = $e->getMessage();
            $this->addFlash('error', 'Problème d\'accès base de données: ' . $errorMessage);
            return $this->redirectToRoute('home_connected');
        }

        // on tri par date-1mois entre +1mois
        $today = strftime('%A %d %B %Y %I:%M:%S');//date du jour
        $dtplus = new \DateTime('now');
        $dtmoins= new \DateTime('now');
        $dtmoins->sub(new DateInterval('P30D'));
        $dtplus->add(new DateInterval('P30D'));
        try {
            $miseEnAvant = $miseEnAvantRepo->filtrer($dtplus, $dtmoins);
        } catch (\Doctrine\DBAL\Exception $e) {
            $errorMessage = $e->getMessage();
            $this->addFlash('error', 'Problème d\'accès base de données: ' . $errorMessage);
            return $this->redirectToRoute('home_connected');
        }
        return $this->render('mise_en_avant/gererMiseEnAvant.html.twig', [
            "dateToday"=>$today, "user"=>$user,"miseEnAvant" => $miseEnAvant, 'dtplus'=>$dtplus, 'dtmoins'=>$dtmoins,
        ]);
    }
    //********************supprimer mise en avant
    /**
     * @Route("/gestion-mise-en-avant/{id}", name="supprimer-misEa")
     */
    public function supprimerMiseEnAvant($id, EntityManagerInterface $em){
        //****************on recupere la miseEnAvant
        try {
            $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
            $miseEnAvant = $miseEnAvantRepo->find($id);
        } catch (\Doctrine\DBAL\Exception $e) {
            $errorMessage = $e->getMessage();
            $this->addFlash('error', 'Problème d\'accès base de données: ' . $errorMessage);
            return $this->redirectToRoute('gérer_mise_en_avant');
        }
        if ($miseEnAvant==null) {
            $this->addFlash('error', 'Une erreur s\'est produite pendant la suppression.');
            return $this->redirectToRoute('gérer_mise_en_avant');
        }
        //********************
        try
        {
            $em->remove($miseEnAvant);
            $em->flush();
            return $this->redirectToRoute('gérer_mise_en_avant');
        } catch (\Doctrine\DBAL\Exception $e)
        {
            $errorMessage = $e->getMessage();
            $this->addFlash('error', 'Erreur lors de la suppression. Nous n\' avons pas pu supprimer la mise en avant/opportunités.'.$errorMessage);
            return $this->redirectToRoute('gérer_mise_en_avant');
        }

    }
//**************************modifier miseEnAvant

    /**
     * @Route("/mise-en-avant/modifier/{id}", name="modifier_mise_en_avant")
     */
    public function modifierMise($id ,EntityManagerInterface $em, Request $request): Response
    {    // on récupère l'user
        $user=$this->getUser();
        //****************on recupere la miseEnAvant
        try {
            $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
            $miseEnAvantSelect = $miseEnAvantRepo->find($id);
        } catch (\Doctrine\DBAL\Exception $e) {
            $errorMessage = $e->getMessage();
            $this->addFlash('error', 'Problème d\'accès base de données: ' . $errorMessage);
            return $this->redirectToRoute('gérer_mise_en_avant');
        }
        //*********creation du formulaire
        $miseEnAvantDeuxForm = $this->createForm(MiseEnAvantDeuxType::class, $miseEnAvantSelect);
        $miseEnAvantDeuxForm->handleRequest($request);
        //**********route**********************************************************
        //************
        if ($miseEnAvantDeuxForm->isSubmitted() and $miseEnAvantDeuxForm->isValid())
        {
            try {
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $em->persist($miseEnAvantSelect);
                $em->flush();
            } catch (\Doctrine\DBAL\Exception $e) {
                $errorMessage = $e->getMessage();
                $this->addFlash('error', 'Nous avons pas pu modifier la mise en avant: ' . $errorMessage);
                return $this->redirectToRoute('gérer_mise_en_avant');
            }
            return $this->redirectToRoute('gérer_mise_en_avant', [

            ]);
        }
        //*********recuperer les mises avant/date en cours
        $today = new \DateTime('now');
        $dtplus = new \DateTime('now');
        $dtmoins= new \DateTime('now');
        $dtmoins->sub(new DateInterval('P1D'));
        $dtplus->add(new DateInterval('P1D'));
        $dtplus->format('Y-m-d');
        $dtmoins->format('Y-m-d');
        $today->format('Y-m-d');
        return $this->render('mise_en_avant/voirMiseEnAvant.html.twig', [
            'miseEnAvantDeuxForm'=>$miseEnAvantDeuxForm->createView(), "user"=>$user, "dateToday"=>$today, 'miseEnAvantSubmit'=>$miseEnAvantSelect,

        ]);

    }
}
