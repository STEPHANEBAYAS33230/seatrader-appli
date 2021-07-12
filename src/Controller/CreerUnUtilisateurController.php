<?php

namespace App\Controller;

use App\Entity\FiltreSociete;
use App\Entity\Utilisateur;
use App\Form\AdminProfilType;
use App\Form\FiltreSocieteType;
use App\Form\ModifUserUtilisateurType;
use App\Form\ModifUtilisateurType;
use App\Form\UtilisateurType;
use DateInterval;
use Doctrine\Bundle\DoctrineBundle\Mapping\EntityListenerServiceResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CreerUnUtilisateurController extends AbstractController
{
    /**
     * @Route("/admin/nouveau-client", name="creerUnUtilisateur")
     */
    public function index(EntityManagerInterface $em, Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        // titre de la page
        $titrePage="Nouveau Client";
        // on récupère l'user
        $user=$this->getUser();
        $today = strftime('%A %d %B %Y %I:%M:%S');
        //$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $participant = new Utilisateur();
        $registerForm = $this->createForm(UtilisateurType::class, $participant);
        $registerForm->handleRequest($request);

        // si formulaire validé
        if ($registerForm->isSubmitted() and $registerForm->isValid()) {

            //hasher le mot de passe avec class passwordEncoderInterface
            $hashed=$encoder->encodePassword($participant,$participant->getPassword());
            $participant->setPassword($hashed);

            //sauvegarder mon utilsateur
            try{
                $em->persist($participant);
                $em->flush();
                $this->addFlash('success', 'le compte a été créé avec succès.');
                return $this->redirectToRoute('home_connected', [
                ]);
            }catch (\Doctrine\DBAL\Exception $e)
            {
                   $errorMessage = $e->getMessage();
                    $this->addFlash('error', 'Nous n\' avons pas pu créer le compte suite un problème/ '.$errorMessage);
                   return $this->redirectToRoute('home_connected', [ ]);
            }


        }
        return $this->render('creer_un_utilisateur/index.html.twig', [
             "registerForm"=>$registerForm->createView(), 'dateToday'=>$today, 'user'=>$user,  'titrePage'=>$titrePage,
        ]);

    }
    //*******************************************************************************
    /**
     * @Route("/admin/clients", name="gerer_mes_clients")
     */
    public function clients (EntityManagerInterface $em, Request $request, UserPasswordEncoderInterface $encoder): Response
    {   $nomfiltre="";
        // on récupère l'user et date du jour
        $user=$this->getUser();
        $today = strftime('%A %d %B %Y %I:%M:%S');
        $dateSept = new \DateTime('now');
        $dateSept->sub(new DateInterval('P7D'));
        // on recupere tous les utilisateurs
        // récupérer la liste des utilisateurs à modifier
        try {
            $utilisateurRepo = $this->getDoctrine()->getRepository(Utilisateur::class);
            $utilisateur = $utilisateurRepo->findAll();
        }catch (\Doctrine\DBAL\Exception $e)
        {
            $errorMessage = $e->getMessage();
            $this->addFlash('error', 'Problème de connexion à la base de données/ '.$errorMessage);
            return $this->redirectToRoute('home_connected--user', [ ]);
        }

        //***********preparation du formulaire filtreSociete
        $filtreSociete = new FiltreSociete();
        $filtreSocieteForm = $this->createForm(FiltreSocieteType::class, $filtreSociete);
        $filtreSocieteForm->handleRequest($request);

        // si formulaire validé
        if ($filtreSocieteForm->isSubmitted() and $filtreSocieteForm->isValid()) {
            $utilisateur= null;
            $utilisateurRepo=null;

                $nomfiltre=$filtreSociete->getNom();
                try {
                    $utilisateurRepo = $this->getDoctrine()->getRepository(Utilisateur::class);
                    $utilisateur = $utilisateurRepo->filtrerSociete($filtreSociete);
                } catch (\Doctrine\DBAL\Exception $e)
                {
                    $errorMessage = $e->getMessage();
                    $this->addFlash('error', 'Problème de connexion à la base de données/erreur: '.$errorMessage);
                    return $this->redirectToRoute('home_connected--user', [ ]);
                }
        }

        return $this->render('gerer_mes_clients/index.html.twig', [
            'dateToday'=>$today, 'user'=>$user, 'utilisateur'=>$utilisateur,"filtreSocieteForm"=>$filtreSocieteForm->createView(), 'filtreSociete'=>$filtreSociete, 'filtrenom'=>$nomfiltre,
            'dateSept'=>$dateSept
        ]);



    }


    /**
     * @Route("/admin/clients/{id}", name="activer_inactiver_utilisateur")
     */
    public function activerInactiverUtilisateur($id, EntityManagerInterface $em,Request $request){
        //****************
        try {
            $utilisateurRepo = $this->getDoctrine()->getRepository(Utilisateur::class);
            $utilisateur = $utilisateurRepo->find($id);
        } catch (\Doctrine\DBAL\Exception $e)
        {
            $errorMessage = $e->getMessage();
            $this->addFlash('error', 'Problème de connection à la base de données: '.$errorMessage);
            return $this->redirectToRoute('gerer_mes_clients', [ ]);
        }
        $etat=$utilisateur->getEtatUtilisateur();
        //********************SI ACTIF -> INACTIF ET AUSSI LE CONTRAIRE
        if ($etat=="ACTIF") {
            $utilisateur->setEtatUtilisateur("INACTIF");
        } else {
            $utilisateur->setEtatUtilisateur("ACTIF");
        }
        try {
            $em->persist($utilisateur);
            $em->flush();
        } catch (\Doctrine\DBAL\Exception $e)
        {
            $errorMessage = $e->getMessage();
            $this->addFlash('error', 'Nous n\'avons pas réussi à inactiver/activer: '.$errorMessage);
            return $this->redirectToRoute('gerer_mes_clients', [ ]);
        }
        return $this->redirectToRoute('gerer_mes_clients');



        //***************************



    }
    /**
     * @Route("/admin/clients/delete/{id}", name="supprimer_utilisateur")
     */
    public function supprimerUtilisateur($id, EntityManagerInterface $em,Request $request){
        //****************
        try {
            $utilisateurRepo = $this->getDoctrine()->getRepository(Utilisateur::class);
            $utilisateur = $utilisateurRepo->find($id);
            $em->remove($utilisateur);
            $em->flush();
        } catch (\Doctrine\DBAL\Exception $e)
        {
            $errorMessage = $e->getMessage();
            $this->addFlash('error', 'Suppression impossible/erreur: '.$errorMessage);
            return $this->redirectToRoute('gerer_mes_clients', [ ]);
        }
        return $this->redirectToRoute('gerer_mes_clients');

    }

    //***********************************************************************************************
    /**
     * @Route("/admin/modification/{id}", name="modifier_UnUtilisateur")
     */
    public function modifierUtilisateur($id,EntityManagerInterface $em, Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $titrePage="Modifier Compte Client";
        // on récupère l'user
        $user=$this->getUser();
        $today = strftime('%A %d %B %Y %I:%M:%S');
        //$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        //*********on recupere le compte de l'utilisateur à modifier
        //****************
        try {
            $utilisateurRepo = $this->getDoctrine()->getRepository(Utilisateur::class);
            $utilisateur = $utilisateurRepo->find($id);
        } catch (\Doctrine\DBAL\Exception $e)
        {
            $errorMessage = $e->getMessage();
            $this->addFlash('error', 'Problème d\'accès à la base de données: '.$errorMessage);
            return $this->redirectToRoute('gerer_mes_clients', [ ]);
        }

        $registerForm = $this->createForm(ModifUtilisateurType::class, $utilisateur);
        $registerForm->handleRequest($request);

        // si formulaire validé
        if ($registerForm->isSubmitted() and $registerForm->isValid()) {

            //hasher le mot de passe avec class passwordEncoderInterface
            $hashed=$encoder->encodePassword($utilisateur,$utilisateur->getPassword());
            $utilisateur->setPassword($hashed);

            //sauvegarder mon utilsateur
            try{
                $em->persist($utilisateur);
                $em->flush();
                $this->addFlash('success', 'le compte a été modifié avec succès');
                return $this->redirectToRoute('gerer_mes_clients', [

                ]);
            }   catch (\Doctrine\DBAL\Exception $e)
            {
                $errorMessage = $e->getMessage();
                $this->addFlash('error', 'Nous n\'avons pas pu modifier le compte client: '.$errorMessage);
                return $this->redirectToRoute('gerer_mes_clients', [ ]);
            }




        }
        return $this->render('creer_un_utilisateur/index.html.twig', [
            "registerForm"=>$registerForm->createView(), 'dateToday'=>$today, 'user'=>$user, 'titrePage'=>$titrePage,
        ]);

    }

    //***********************************************************************************************
    /**
     * @Route("/monAppli/compte", name="modifier_monprofilUser")
     */
    public function  modifierUtilisateurUser(EntityManagerInterface $em, Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $titrePage="Modifier mon Compte";
        // on récupère l'user
        $user=$this->getUser();
        $id=$user->getId();
        $role=$user->getRoles();
        $today = strftime('%A %d %B %Y %I:%M:%S');
        //*********on recupere le compte de l'utilisateur à modifier
        //****************
        try {
            $utilisateurRepo = $this->getDoctrine()->getRepository(Utilisateur::class);
            $utilisateur = $utilisateurRepo->find($id);
        } catch (\Doctrine\DBAL\Exception $e)
        {
            $errorMessage = $e->getMessage();
            $this->addFlash('error', 'Nous n\'avons pas pu modifier le compte client: '.$errorMessage);
            if ($role == ['ROLE_ADMIN']) {
                $this->denyAccessUnlessGranted('ROLE_ADMIN');
                $this->addFlash('success', 'Nous n\'avons pas pu modifier le compte client: '.$errorMessage);
                return $this->redirectToRoute('gerer_mes_clients', [
                ]);
            }
            return $this->redirectToRoute('home_connected', [ ]);
        }

        $registerForm = $this->createForm(ModifUserUtilisateurType::class, $utilisateur);
        $registerForm->handleRequest($request);

        // si formulaire validé
        if ($registerForm->isSubmitted() and $registerForm->isValid()) {
            /*
            //hasher le mot de passe avec class passwordEncoderInterface
            $hashed=$encoder->encodePassword($utilisateur,$utilisateur->getPassword());
            $utilisateur->setPassword($hashed);*/

            //sauvegarder mon utilsateur
            //try{
            try
            {
            $em->persist($utilisateur);
            $em->flush();
            } catch (\Doctrine\DBAL\Exception $e)
            {
                $errorMessage = $e->getMessage();
                $this->addFlash('error', 'Nous n\'avons pas pu modifier le compte client: '.$errorMessage);
                if ($role ==['ROLE_ADMIN']) {
                return $this->redirectToRoute('gerer_mes_clients', [ ]);}
                if ($role ==['ROLE_USER']) {
                    return $this->redirectToRoute('home_connected', [ ]);}
            }
            if ($role == ['ROLE_ADMIN']) {
                $this->addFlash('success', 'le compte a été modifié avec succès');
                return $this->redirectToRoute('gerer_mes_clients', [

                ]);
            }
            else
            {//si role=user
                return $this->redirectToRoute('home_connected', [

                ]);
            }



        }
        return $this->render('creer_un_utilisateur/modifUserUtilisateur.html.twig', [
            "registerForm"=>$registerForm->createView(), 'dateToday'=>$today, 'user'=>$user, 'titrePage'=>$titrePage,
        ]);

    }

    //***********************************************************************************************
    /**
     * @Route("/admin/monCompte/monProfil", name="modif_cpte_adminas")
     */
    public function modifierAdmin(UserInterface $user,EntityManagerInterface $em, Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        // on récupère l'user

        //$user=$this->getUser();
        $id=$user->getId();
        try {
            $utilisateurRepo = $this->getDoctrine()->getRepository(Utilisateur::class);
            $utilisateur = $utilisateurRepo->find($id);
        } catch (\Doctrine\DBAL\Exception $e)
        {
            $errorMessage = $e->getMessage();
            $this->addFlash('error', 'Problème d\'accès à la base de données: '.$errorMessage);
            return $this->redirectToRoute('home_connected', [ ]);
        }
        $today = strftime('%A %d %B %Y %I:%M:%S');
        //$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        //*********on recupere le compte de l'utilisateur à modifier
        //****************

        $registerForm = $this->createForm(AdminProfilType::class, $utilisateur);
        $registerForm->handleRequest($request);

        // si formulaire validé
        if ($registerForm->isSubmitted() and $registerForm->isValid()) {

            //hasher le mot de passe avec class passwordEncoderInterface
            $hashed=$encoder->encodePassword($utilisateur,$utilisateur->getPassword());
            $utilisateur->setPassword($hashed);

            //sauvegarder mon utilsateur
            //try{
            try
            {
                $em->persist($utilisateur);
                $em->flush();
                $this->addFlash('error', 'Le compte a été modifié avec succès');
                return $this->redirectToRoute('home_connected', [

                ]);
            } catch (\Doctrine\DBAL\Exception $e)
            {
                $errorMessage = $e->getMessage();
                $this->addFlash('error', 'Nous n\'avons pas pu modifier le compte: '.$errorMessage);
                return $this->redirectToRoute('home_connected', [ ]);
            }
        }
        $titrePage="Modifier mon compte administrateur";
        return $this->render('creer_un_utilisateur/index.html.twig', [
            "registerForm"=>$registerForm->createView(), 'dateToday'=>$today, 'user'=>$user, 'titrePage'=>$titrePage,
        ]);
    }

    //***********************************************************************************************
}
