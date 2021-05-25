<?php

namespace App\Controller;

use App\Entity\FiltreSociete;
use App\Entity\Utilisateur;
use App\Form\AdminProfilType;
use App\Form\FiltreSocieteType;
use App\Form\ModifUserUtilisateurType;
use App\Form\ModifUtilisateurType;
use App\Form\UtilisateurType;
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
            //try{

            $em->persist($participant);
            $em->flush();
            $this->addFlash('success', 'le compte a été créé avec succès (veuillez-vous connecter maintenant)');
            return $this->redirectToRoute('home_connected', [

            ]);

            //} catch (\Doctrine\DBAL\Exception $e) {
            //    $errorMessage = $e->getMessage();
            //   echo ($errorMessage);
            //  $this->addFlash('error', 'Nous n\' avons pas pu créer le compte (email existant...etc)');
            // return $this->redirectToRoute('home', [
            //   'controller_name' => 'HomeController',
            //  ]);
            // }

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
        // on recupere tous les utilisateurs
        // récupérer la liste des utilisateurs à modifier
        $utilisateurRepo = $this->getDoctrine()->getRepository(Utilisateur::class);
        $utilisateur = $utilisateurRepo->findAll();
        //***********preparation du formulaire filtreSociete
        $filtreSociete = new FiltreSociete();
        $filtreSocieteForm = $this->createForm(FiltreSocieteType::class, $filtreSociete);
        $filtreSocieteForm->handleRequest($request);

        // si formulaire validé
        if ($filtreSocieteForm->isSubmitted() and $filtreSocieteForm->isValid()) {
            $utilisateur= null;
            $utilisateurRepo=null;

                $nomfiltre=$filtreSociete->getNom();
                $utilisateurRepo = $this->getDoctrine()->getRepository(Utilisateur::class);
                $utilisateur = $utilisateurRepo->filtrerSociete($filtreSociete);



        }

        return $this->render('gerer_mes_clients/index.html.twig', [
            'dateToday'=>$today, 'user'=>$user, 'utilisateur'=>$utilisateur,"filtreSocieteForm"=>$filtreSocieteForm->createView(), 'filtreSociete'=>$filtreSociete, 'filtrenom'=>$nomfiltre,
        ]);



    }


    /**
     * @Route("/admin/clients/{id}", name="activer_inactiver_utilisateur")
     */
    public function activerInactiverUtilisateur($id, EntityManagerInterface $em,Request $request){
        //****************
        $utilisateurRepo = $this->getDoctrine()->getRepository(Utilisateur::class);
        $utilisateur = $utilisateurRepo->find($id);
        $etat=$utilisateur->getEtatUtilisateur();
        //********************SI ACTIF -> INACTIF ET AUSSI LE CONTRAIRE
        if ($etat=="ACTIF") {
            $utilisateur->setEtatUtilisateur("INACTIF");
        } else {
            $utilisateur->setEtatUtilisateur("ACTIF");
        }
        $em->persist($utilisateur);
        $em->flush();

        return $this->redirectToRoute('gerer_mes_clients');



        //***************************



    }
    /**
     * @Route("/admin/clients/delete/{id}", name="supprimer_utilisateur")
     */
    public function supprimerUtilisateur($id, EntityManagerInterface $em,Request $request){
        //****************
        $utilisateurRepo = $this->getDoctrine()->getRepository(Utilisateur::class);
        $utilisateur = $utilisateurRepo->find($id);
        $em->remove($utilisateur);
        $em->flush();

        return $this->redirectToRoute('gerer_mes_clients');

    }

    //***********************************************************************************************
    /**
     * @Route("/admin/{id}", name="modifier_UnUtilisateur")
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
        $utilisateurRepo = $this->getDoctrine()->getRepository(Utilisateur::class);
        $utilisateur = $utilisateurRepo->find($id);


        $registerForm = $this->createForm(ModifUtilisateurType::class, $utilisateur);
        $registerForm->handleRequest($request);

        // si formulaire validé
        if ($registerForm->isSubmitted() and $registerForm->isValid()) {

            //hasher le mot de passe avec class passwordEncoderInterface
            $hashed=$encoder->encodePassword($utilisateur,$utilisateur->getPassword());
            $utilisateur->setPassword($hashed);

            //sauvegarder mon utilsateur
            //try{

            $em->persist($utilisateur);
            $em->flush();
            $this->addFlash('success', 'le compte a été créé avec succès (veuillez-vous connecter maintenant)');
            return $this->redirectToRoute('home_connected', [

            ]);



        }
        return $this->render('creer_un_utilisateur/index.html.twig', [
            "registerForm"=>$registerForm->createView(), 'dateToday'=>$today, 'user'=>$user, 'titrePage'=>$titrePage,
        ]);

    }

    //***********************************************************************************************
    /**
     * @Route("/compte", name="modifier_monprofilUser")
     */
    public function modifierUtilisateurUser(EntityManagerInterface $em, Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $titrePage="Modifier mon Compte";
        // on récupère l'user
        $user=$this->getUser();
        $id=$user->getId();
        $today = strftime('%A %d %B %Y %I:%M:%S');
        //$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        //*********on recupere le compte de l'utilisateur à modifier
        //****************
        $utilisateurRepo = $this->getDoctrine()->getRepository(Utilisateur::class);
        $utilisateur = $utilisateurRepo->find($id);


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

            $em->persist($utilisateur);
            $em->flush();
            $this->addFlash('success', 'le compte a été modifié avec succès (veuillez-vous connecter');
            return $this->redirectToRoute('home_connected_user', [

            ]);



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
        $utilisateurRepo = $this->getDoctrine()->getRepository(Utilisateur::class);
        $utilisateur = $utilisateurRepo->find($id);

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

            $em->persist($utilisateur);
            $em->flush();
            return $this->redirectToRoute('home_connected', [

            ]);



        }
        $titrePage="Modifier mon compte administrateur";
        return $this->render('creer_un_utilisateur/index.html.twig', [
            "registerForm"=>$registerForm->createView(), 'dateToday'=>$today, 'user'=>$user, 'titrePage'=>$titrePage,
        ]);

    }

    //***********************************************************************************************
}
