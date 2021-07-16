<?php

namespace App\Controller;

use App\Entity\Code;
use App\Entity\Commande;
use App\Entity\EtatCommande;
use App\Entity\MiseEnAvant;
use App\Form\CodeType;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
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
            return $this->redirectToRoute('home_connected--user--code');
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
        public
        function connectedAdmin(EntityManagerInterface $em): Response
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
     * @Route("/admin/home/code-admin", name="home_connected--user--code")
     */
    public
    function connectedAdminCode(EntityManagerInterface $em,  MailerInterface $mailer, Environment $twig, Request $request): Response
    {
        $nbreHasard=rand(10000,99999);
        // si l'utilisateur est admin VVVVVVV (route si dessous)
        // on récupère l'user et son role
        $user = $this->getUser();
        //******envoi du code à contact@seatrader.eu
        //envoi mail
        $email = (new TemplatedEmail())
            ->from('contact@seatrader.eu')
            ->to('contact@seatrader.eu')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            ->priority(Email::PRIORITY_HIGH)
            ->subject('code authentification')
            ->text('Veuillez saisir ce code pour vous authentification: '.$nbreHasard)
            ->htmlTemplate( 'mail/mail.html.twig');
        $mailer->send($email);
        $code= new Code();
        $codeForm = $this->createForm(CodeType::class, $code);
        $codeForm->handleRequest($request);
        // si formulaire validé
        if ($codeForm->isSubmitted() and $codeForm->isValid()) {





        }
        return $this->render('code/code.html.twig', [ 'codeForm' => $codeForm->createView(),]);


    }
    }
