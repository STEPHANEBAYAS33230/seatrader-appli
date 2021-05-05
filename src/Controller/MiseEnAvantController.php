<?php

namespace App\Controller;

use App\Entity\MiseEnAvant;
use App\Form\MiseEnAvantDeuxType;
use App\Form\MiseEnAvantType;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Mixed_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MiseEnAvantController extends AbstractController
{
    /**
     * @Route("/mise-en-avant", name="creer_mise_en_avant")
     */
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        // on récupère l'user
        $user=$this->getUser();
        // les dates
        //*********recuperer les mises avant/date en cours
        $today = new \DateTime('now');
        $dtplus = new \DateTime('now');
        $dtmoins= new \DateTime('now');
        $dtmoins->sub(new DateInterval('P1D'));
        $dtplus->add(new DateInterval('P1D'));
        $dtplus->format('Y-m-d');
        $dtmoins->format('Y-m-d');
        $today->format('Y-m-d');


        for($i=1;$i<31;$i++) {
            $today->add(new DateInterval('P1D'));
            $dtmoins->add(new DateInterval('P1D'));
            $dtplus->add(new DateInterval('P1D'));

            // récupère repository
            $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
            $miseEnAvant = $miseEnAvantRepo->filtrer($dtplus, $dtmoins);
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
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $miseEnAvant = $miseEnAvantRepo->filtrer($dtplus, $dtmoins);
                if (!empty($miseEnAvant)){
                    break;
                }

            }
            //***********fin de IF
        }
        $today = new \DateTime('now');
        $todayPlus = new \DateTime('now');
        $todayPlus->add(new DateInterval('P1D'));
        //******************************************************
        //**********creation d une nouvelle mise en avant
        $miseEnAvantSelect= new MiseEnAvant();
        // on hydrate la nouvelle mise en avant
        $miseEnAvantSelect->setDateCreation($today);
        $miseEnAvantSelect->setDateLivraisonMiseEnAvant($todayPlus);

        //*********creation du formulaire
        $miseEnAvantForm = $this->createForm(MiseEnAvantType::class, $miseEnAvantSelect);
        $miseEnAvantForm->handleRequest($request);
        //*********creation du formulaire
        $miseEnAvantDeuxForm = $this->createForm(MiseEnAvantDeuxType::class, $miseEnAvantSelect);
        $miseEnAvantDeuxForm->handleRequest($request);
        //**********route**********************************************************
        if (($miseEnAvantForm->isSubmitted() and $miseEnAvantForm->isValid()) or ($miseEnAvantDeuxForm->isSubmitted() and $miseEnAvantDeuxForm->isValid()))
        {

            //************
            if ($miseEnAvantDeuxForm->isSubmitted() and $miseEnAvantDeuxForm->isValid())
            {
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $em->persist($miseEnAvantSelect);
                $em->flush();
                return $this->redirectToRoute('creer_mise_en_avant', [

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


            for($i=1;$i<31;$i++) {
                $today->add(new DateInterval('P1D'));
                $dtmoins->add(new DateInterval('P1D'));
                $dtplus->add(new DateInterval('P1D'));

                // récupère repository
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $miseEnAvant = $miseEnAvantRepo->filtrer($dtplus, $dtmoins);
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
                    $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                    $miseEnAvant = $miseEnAvantRepo->filtrer($dtplus, $dtmoins);
                    if (!empty($miseEnAvant)){
                        break;
                    }

                }
                //***********fin de IF
            }
            $today = new \DateTime('now');
            $todayPlus = new \DateTime('now');
            $todayPlus->add(new DateInterval('P1D'));
            //*************************************fin mise en avant en cours ou passée
            return $this->render('mise_en_avant/voirMiseEnAvant.html.twig', [
                'miseEnAvantDeuxForm'=>$miseEnAvantDeuxForm->createView(), "user"=>$user, "dateToday"=>$today, 'miseEnAvantSubmit'=>$miseEnAvantSelect,
                'miseEnAvant'=>$miseEnAvant,
            ]);
        }

            return $this->render('mise_en_avant/creerMiseEnAvant.html.twig', [
            "dateToday"=>$today, "user"=>$user,"miseEnAvant" => $miseEnAvant, "miseEnAvantForm"=>$miseEnAvantForm->createView()
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
        $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
        // on tri par date-1mois entre +1mois
        $today = strftime('%A %d %B %Y %I:%M:%S');//date du jour
        $dtplus = new \DateTime('now');
        $dtmoins= new \DateTime('now');
        $dtmoins->sub(new DateInterval('P30D'));
        $dtplus->add(new DateInterval('P30D'));
        $miseEnAvant = $miseEnAvantRepo->filtrer($dtplus, $dtmoins);






        return $this->render('mise_en_avant/gererMiseEnAvant.html.twig', [
            "dateToday"=>$today, "user"=>$user,"miseEnAvant" => $miseEnAvant, 'dtplus'=>$dtplus, 'dtmoins'=>$dtmoins,
        ]);
    }

    /**
     * @Route("/gestion-mise-en-avant/{id}", name="supprimer-misEa")
     */
    public function supprimerMiseEnAvant($id, EntityManagerInterface $em){
        //****************on recupere la miseEnAvant
        $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
        $miseEnAvant = $miseEnAvantRepo->find($id);

        //********************
        $em->remove($miseEnAvant);
        $em->flush();
        return $this->redirectToRoute('gérer_mise_en_avant');
    }
//**************************modifier miseEnAvant

    /**
     * @Route("/mise-en-avant/modifier/{id}", name="modifier_mise_en_avant")
     */
    public function modifierMise($id ,EntityManagerInterface $em, Request $request): Response
    {    // on récupère l'user
        $user=$this->getUser();
        //****************on recupere la miseEnAvant
        $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
        $miseEnAvantSelect = $miseEnAvantRepo->find($id);
        //*********creation du formulaire
        $miseEnAvantDeuxForm = $this->createForm(MiseEnAvantDeuxType::class, $miseEnAvantSelect);
        $miseEnAvantDeuxForm->handleRequest($request);
        //**********route**********************************************************


            //************
            if ($miseEnAvantDeuxForm->isSubmitted() and $miseEnAvantDeuxForm->isValid())
            {
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $em->persist($miseEnAvantSelect);
                $em->flush();
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


            for($i=1;$i<31;$i++) {
                $today->add(new DateInterval('P1D'));
                $dtmoins->add(new DateInterval('P1D'));
                $dtplus->add(new DateInterval('P1D'));

                // récupère repository
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $miseEnAvant = $miseEnAvantRepo->filtrer($dtplus, $dtmoins);
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
                    $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                    $miseEnAvant = $miseEnAvantRepo->filtrer($dtplus, $dtmoins);
                    if (!empty($miseEnAvant)){
                        break;
                    }

                }
                //***********fin de IF
            }
            $today = new \DateTime('now');
            $todayPlus = new \DateTime('now');
            $todayPlus->add(new DateInterval('P1D'));
            //*************************************fin mise en avant en cours ou passée


            return $this->render('mise_en_avant/voirMiseEnAvant.html.twig', [
                'miseEnAvantDeuxForm'=>$miseEnAvantDeuxForm->createView(), "user"=>$user, "dateToday"=>$today, 'miseEnAvantSubmit'=>$miseEnAvantSelect,
                'miseEnAvant'=>$miseEnAvant,
            ]);



    }


}
