<?php

namespace App\Controller;

use App\Entity\Code;
use App\Entity\Commande;
use App\Entity\EtatCommande;
use App\Entity\Identification;
use App\Entity\MiseEnAvant;
use App\Entity\Utilisateur;
use App\Form\CodeType;
use App\Form\IdentificationType;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;


class HomeConnectedController extends AbstractController
{
    /**
     * @Route("/monAppli/home/connected", name="home_connected")
     */
    public function index(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        //maintenance effacer les miseenavant de plus d'un mois
        // récupère repository
        $dtplus = new \DateTime('now');
        $dtmoins = new \DateTime('now');
        $dtmoins->sub(new DateInterval('P365D'));
        $dtplus->sub(new DateInterval('P30D'));
        try {
            $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
            $miseEnAvant = $miseEnAvantRepo->filtrer($dtplus, $dtmoins);
        } catch (\Doctrine\DBAL\Exception $e) {
            $errorMessage = $e->getMessage();
            $this->addFlash('error', 'Problème d\'accès à la base de données:' . $errorMessage);
            return $this->redirectToRoute('app_logout');
        }
        //********************on boucle les miseEnavant à effacer
        foreach ($miseEnAvant as $mea) {
            try {
                $em->remove($mea);
                $em->flush();
            } catch (\Doctrine\DBAL\Exception $e) {
                $errorMessage = $e->getMessage();
                $this->addFlash('error', 'Erreur maintenance Mise en Avant:' . $errorMessage);
                return $this->redirectToRoute('app_logout');
            }
        }
            //**************************************************
            //maintenance passer les cde recues et traitées de+ d'un mois en archive
            // récupère repository
            // recupere l etat envoyee
            try {
                $etatRepo = $this->getDoctrine()->getRepository(EtatCommande::class);
                $etat = $etatRepo->find(4);
                $cdeRepo = $this->getDoctrine()->getRepository(Commande::class);
                $cde = $cdeRepo->passerArchive($etat);
                //********************on boucle les cde et on les change en etat archivee
                $etatRepo = $this->getDoctrine()->getRepository(EtatCommande::class);
                $etat = $etatRepo->find(1); //on recupere etat archivée
            } catch (\Doctrine\DBAL\Exception $e) {
                $errorMessage = $e->getMessage();
                $this->addFlash('error', 'Problème d\'accès à la base de données:' . $errorMessage);
                return $this->redirectToRoute('app_logout');
            }
            try {
                foreach ($cde as $value) {
                    $value->setEtatCommande($etat);
                    $em->persist($value);
                }
                $em->flush();
            } catch (\Doctrine\DBAL\Exception $e) {
                $errorMessage = $e->getMessage();
                $this->addFlash('error', 'Erreur maintenance état commande:' . $errorMessage);
                return $this->redirectToRoute('app_logout');
            }
            //**************************************************
            //**************************************************
            //maintenance passer les cde archivees de+ de 2MOIS à effacer
            // récupère repository
            // recupere l etat envoyee
            try {
                $etatRepo = $this->getDoctrine()->getRepository(EtatCommande::class);
                $etat = $etatRepo->find(1); //on recuper l etat archivee
                $cdeRepo = $this->getDoctrine()->getRepository(Commande::class);
                $cde = $cdeRepo->effacerArchive($etat);
            } catch (\Doctrine\DBAL\Exception $e) {
                $errorMessage = $e->getMessage();
                $this->addFlash('error', 'Problème d\'accès à la base de données:' . $errorMessage);
                return $this->redirectToRoute('app_logout');
            }

            try {
                foreach ($cde as $value) {
                    $em->remove($value);
                }
                $em->flush();
            } catch (\Doctrine\DBAL\Exception $e) {
                $errorMessage = $e->getMessage();
                $this->addFlash('error', 'Erreur maintenance cde archivée/supprimée:' . $errorMessage);
                return $this->redirectToRoute('app_logout');
            }
            //**************************************************


            //***************************************
            // on récupère l'user et son role
            $user = $this->getUser();
            $role = $user->getRoles();

            $role = $user->getRoles();
            if ($role == ['ROLE_USER']) {
                if ($user->getEtatUtilisateur() == "INACTIF") {
                    $this->addFlash('error', "Compte désactivé (veuillez contacter l'administrateur)");
                    return $this->redirectToRoute('app_logout');
                }
                return $this->redirectToRoute('home_connected_user');
            }

            $this->denyAccessUnlessGranted('ROLE_ADMIN');
            return $this->redirectToRoute('home_connected--user');
            $today = strftime('%A %d %B %Y %I:%M:%S');
            // si l'utilisateur est admin VVVVVVV (route si dessous)
            // on récupère l'user et son role
            $user = $this->getUser();
            //******recuperer les cdes envoyée
            //****************on recupere la cde
        try {
            $cdeRepo = $this->getDoctrine()->getRepository(Commande::class);
            $commandesEnvoyees = $cdeRepo->filtreCdeStatut(2);
            $commandesReceptionnee = $cdeRepo->filtreCdeStatut(3);
            $commandesTraitee = $cdeRepo->filtreCdeStatut(4);
            $commandesArchivee = $cdeRepo->filtreCdeStatut(1);
        } catch (\Doctrine\DBAL\Exception $e) {
            $errorMessage = $e->getMessage();
            $this->addFlash('error', 'Problème d\'accès à la base de données: ' . $errorMessage);
            return $this->redirectToRoute('app_logout');
        }

            return $this->render('home_connected/homeAdmin.html.twig', [
                'dateToday' => $today, "user" => $user, 'commandesEnvoyees' => $commandesEnvoyees, 'commandesReceptionnee' => $commandesReceptionnee,
                'commandesTraitee' => $commandesTraitee, 'commandesArchivee' => $commandesArchivee,
            ]);

        }

        /**
         * @Route("/admin/home/connected-admin", name="home_connected--user")
         */
        public function connectedAdmin(EntityManagerInterface $em): Response
        {
            $today = strftime('%A %d %B %Y %I:%M:%S');
            // si l'utilisateur est admin VVVVVVV (route si dessous)
            // on récupère l'user et son role
            $user = $this->getUser();
            //******recuperer les cdes envoyée
            //****************on recupere la cde
            try {
                $cdeRepo = $this->getDoctrine()->getRepository(Commande::class);
                $commandesEnvoyees = $cdeRepo->filtreCdeStatut(2);
                $commandesReceptionnee = $cdeRepo->filtreCdeStatut(3);
                $commandesTraitee = $cdeRepo->filtreCdeStatut(4);
                $commandesArchivee = $cdeRepo->filtreCdeStatut(1);
            } catch (\Doctrine\DBAL\Exception $e) {
                $errorMessage = $e->getMessage();
                $this->addFlash('error', 'Problème d\'accès à la base de données: ' . $errorMessage);
                return $this->redirectToRoute('app_logout');
            }

            return $this->render('home_connected/homeAdmin.html.twig', [
                'dateToday' => $today, "user" => $user, 'commandesEnvoyees' => $commandesEnvoyees, 'commandesReceptionnee' => $commandesReceptionnee,
                'commandesTraitee' => $commandesTraitee, 'commandesArchivee' => $commandesArchivee,
            ]);


        }

    /**
     * @Route("/home/identifications", name="identifications")
     */
    public function identif(EntityManagerInterface $em,  MailerInterface $mailer, Environment $twig, Request $request, AuthenticationUtils $authenticationUtils): Response
    {
       $utili=new Utilisateur();
        $identificationForm = $this->createForm(IdentificationType::class, $utili);
        $identificationForm->handleRequest($request);
        // si formulaire validé
        if ($identificationForm->isSubmitted() and $identificationForm->isValid()) {
            $identifiant=$utili->getNomDeLaSociete();
            try{
                $utilisateurRepo = $this->getDoctrine()->getRepository(Utilisateur::class);
                $utilisateur = $utilisateurRepo->trouverUtilisateur($identifiant." ");
            } catch (\Doctrine\DBAL\Exception $e)
            {
                $this->addFlash('error', 'Problème d\'accès à la base de données: ');
                return $this->redirectToRoute('/');
            }
            $nomm=$utilisateur[0]->getNomDeLaSociete();
            $roles=$utilisateur[0]->getRoles();


            if ($utilisateur!=null and $nomm==$identifiant." " and $roles==['ROLE_USER']) { return $this->redirectToRoute('app_login',['identifiant'=>$identifiant]);
            }

            try{
                $utilisateurRepo = $this->getDoctrine()->getRepository(Utilisateur::class);
                $utilisateur = $utilisateurRepo->trouverAdminis($identifiant." ");
            } catch (\Doctrine\DBAL\Exception $e)
            {
                $this->addFlash('error', 'Problème d\'accès à la base de données: ');
                return $this->redirectToRoute('/');
            }
            if ($utilisateur==null){

                $this->addFlash('error', 'Identifiant inconnu');
                return $this->redirectToRoute('home');
            }
            $nomm=$utilisateur[0]->getNomDeLaSociete();
            $roles=$utilisateur[0]->getRoles();
            if ($utilisateur!=null and $nomm==$identifiant." " and $roles==['ROLE_ADMIN', 'ROLE_USER']) {
                $nombre=rand(10000,99999);
                //envoi mail
                $email = (new TemplatedEmail())
                    ->from('contact@seatrader.eu')
                    ->to('contact@seatrader.eu')
                    //->cc('cc@example.com')
                    //->bcc('bcc@example.com')
                    //->replyTo('fabien@example.com')
                    ->priority(Email::PRIORITY_HIGH)
                    ->subject('code accès seatrader-appli')
                    ->text('Voici le code d\'accès pour identifier sur site seatrader-appli: '.$nombre)
                    ->htmlTemplate( 'mail/mail.html.twig');
                $mailer->send($email);
               if (1==1) {
                   return $this->redirectToRoute('app_login', ['identifiant' => $identifiant]);
               }
                $nomDLS="";
                return $this->render('security/login2.html.twig', ['identifiant'=>$identifiant, 'last_username' => $lastUsername, 'error' => $error,
                    'nomDLS'=>$nomDLS,'nombre'=>$nombre]);
            }



        }
        return $this->render('identification/identification.html.twig', [ 'identificationForm' => $identificationForm->createView(),]);


    }
    }
