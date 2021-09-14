<?php

namespace App\Controller;

use App\Entity\CalendrierLivraison;
use App\Entity\Commande;
use App\Entity\EtatCommande;
use App\Entity\FamilleProduit;
use App\Entity\MiseEnAvant;
use App\Entity\Produit;
use App\Entity\Utilisateur;
use App\Form\CommandeEtatType;
use App\Form\CommandeType;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
{
    /**
     * @Route("/monAppli/commande", name="faire_cde")
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param MailerInterface $mailer
     * @return Response
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function index(EntityManagerInterface $em, Request $request,  MailerInterface $mailer): Response
    {

        // recupere toutes les familles/produits
        try {
            $familleProduitRepo = $this->getDoctrine()->getRepository(FamilleProduit::class);
            $familleProduit = $familleProduitRepo->findAll();
        } catch (\Doctrine\DBAL\Exception $e)
        {

            $this->addFlash('error', 'Problème d\'accès àla base de données '.'commandecontroller43');
            return $this->redirectToRoute('home_connected', [ ]);
        }
        $produitRepo = $this->getDoctrine()->getRepository(Produit::class);
        $produits = $produitRepo->findAll();

        //***********mettre les quantité à zero pour les produits
        foreach( $produits as $prd ) {
            $prd->setQuantite(0);
        }
        //********************************


        // creer une formulaire
        //************formulaire
        $cde=new Commande();
        $commande = new Commande();

        //*******recup datejour et user
        // on récupère l'user
        $user=$this->getUser();
        $today = new \DateTime('now');


        $JLiv = new \DateTime('now');
        $JLiv->add(new DateInterval('P1D'));


        //hydrate le formulaire
        $commande->setDateCreationCommande($today);
        $commande->setJourDeLivraison($JLiv);
        $commande->setUtilisateur($user);
        //foreach( $produits as $prd ) {
        //$commande->add($prd);}
        //************toutes les miseENavant apres la date du jour
        $todey=new \DateTime('now');
        $todey->add(new DateInterval('P1D'));
        $todayTrente = new \DateTime('now');
        $todayTrente->add(new DateInterval('P30D'));
        try{
            $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
            $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
        } catch (\Doctrine\DBAL\Exception $e)
        {

            $this->addFlash('error', 'Problème d\'accès àla base de données '.'commandecontroller87');
            return $this->redirectToRoute('home_connected', [ ]);
        }

        //******************************************
        $commandeForm = $this->createForm(CommandeType::class, $commande);
        $commandeForm->handleRequest($request);
        if ($commandeForm->isSubmitted() and $commandeForm->isValid()) {
            //*******verif des dates et ouverture des cde
            $dateVoulue=($commande->getJourDeLivraison());
            $dateVoulue2=($commande->getJourDeLivraison());

            //******************** jour en francais
            $dtt=date_format($dateVoulue,"d/m/Y");
            $dtt2=date_format($dateVoulue2,"Y-m-d");

            //$dtt=$dateVoulue;
            // tableau des jours de la semaine date_format($date,"Y/m/d H:i:s");
            //$dtt=date("d/m/Y");
            //********connaitre le jour de la semaine
            $joursem = array('dim', 'lun', 'mar', 'mer', 'jeu', 'ven', 'sam');
            // extraction des jour, mois, an de la date
            list($jour, $mois, $annee) = explode('/', $dtt);
            // calcul du timestamp
            $timestamp = mktime (0, 0, 0, $mois, $jour, $annee);
            // affichage du jour de la semaine
            $jourSem= $joursem[date("w",$timestamp)];
            //***************si message non changé
            if ( $commande->getNote()=="message à remplir juste avant de clicker sur enregister et envoyer (impératif)." ) {
                //************toutes les miseENavant apres la date du jour
                $todey=$commande->getJourDeLivraison();//ligneajoutee
                $todayTrente = new \DateTime('now');
                $todayTrente->add(new DateInterval('P30D'));
                try{
                    $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                    $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
                }catch (\Doctrine\DBAL\Exception $e)
                {
                    $this->addFlash('error', 'Problème d\'accès àla base de données '.'commandecontroller126');
                    return $this->redirectToRoute('home_connected', [ ]);
                }

                //******************************************
                $this->addFlash('error', "commande en cours (note à changer juste avant l'envoi de la commande (impératif))");
                $commandeForm = $this->createForm(CommandeType::class, $commande);
                $commandeForm->handleRequest($request);
                foreach( $produits as $prd ) {
                    $prd->setQuantite($request->request->get('prod'.(string)$prd->getId(),0));

                }
                return $this->render('commande/index.html.twig',[ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
                    "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde, 'miseEnAvant'=>$miseEnAvant,  "dateTodey"=>$todey,

                ]);
            }
            //*********si jour livraison egale à dim ou lundi pas de cde possible
            //******************************
            //** MANQUE VOIR SI DATE OUVERTE(pour dim/et lun) ou bloqué(pour mar/mer/jeu/ven/sam) PAR ADMIN
            try {
                $calendrierLivRepo = $this->getDoctrine()->getRepository(CalendrierLivraison::class);
                $blocCalendrierLiv=$calendrierLivRepo->filtreDateBloc($dtt2);
                $openCalendrierLiv=$calendrierLivRepo->filtreDateOpen($dtt2);
            } catch (\Doctrine\DBAL\Exception $e)
            {
                $this->addFlash('error', 'Problème d\'accès àla base de données '.'commandecontroller153');
                return $this->redirectToRoute('home_connected', [ ]);
            }

            //******************************
            if (($jourSem=="dim" or $jourSem=="lun" or sizeof($blocCalendrierLiv)>0) and sizeof($openCalendrierLiv)<1) {
                //************toutes les miseENavant apres la date du jour
                $todey=$commande->getJourDeLivraison();//ligneajoutee
                $todayTrente = new \DateTime('now');
                $todayTrente->add(new DateInterval('P30D'));
                try {
                    $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                    $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
                } catch (\Doctrine\DBAL\Exception $e)
                {
                    $this->addFlash('error', 'Problème d\'accès àla base de données '.'commandecontroller169');
                    return $this->redirectToRoute('home_connected', [ ]);
                }
                //******************************************
                $this->addFlash('error', "La livraison n'est pas ouverte pour ce jour là.");
                $commandeForm = $this->createForm(CommandeType::class, $commande);
                $commandeForm->handleRequest($request);
                foreach( $produits as $prd ) {
                    $prd->setQuantite($request->request->get('prod'.(string)$prd->getId(),0));

                }
                return $this->render('commande/index.html.twig',[ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
                    "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde, 'miseEnAvant'=>$miseEnAvant,  "dateTodey"=>$todey,

                ]);
            }
            //**********controle si date du jour <24h date de liv
            $dttjour=date("d");
            $dttmois=date("m");
            $dttan=date("Y");
            $dtjour2=new \DateTime('now');
            $dtjour2=date_format($dtjour2,"Y-m-d");
            //$diff = round((strtotime($dtt) - strtotime($dtjour))/(60*60*24)-1);
            $heure=date('H');

            //calcul de la diff
            // jour mois annee date de liv choisie
            $start = new DateTimeImmutable($dtjour2);//date de départ
            $end = new DateTimeImmutable($dtt2);//date de départ
            $interval = $start->diff($end);//on récupère la différence entre ces 2 dates
            $diff=$interval->format('%a');//affiche : en jours
            if(intval($jour)<intval($dttjour) and intval($mois)==intval($dttmois) and intval($annee)==intval($dttan)){
                $diff=$diff*-1;
            } else  if( intval($mois)<intval($dttmois) and intval($annee)==intval($dttan)) { $diff=$diff*-1;}
            else  if( intval($annee)<intval($dttan)) { $diff=$diff*-1;}

            if ($diff<=0) {
                //************toutes les miseENavant apres la date du jour
                $todey=$commande->getJourDeLivraison();//ligneajoutee
                $todayTrente = new \DateTime('now');
                $todayTrente->add(new DateInterval('P30D'));
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
                //******************************************
                $this->addFlash('error', "Erreur Date de livraison.");

                $commandeForm = $this->createForm(CommandeType::class, $commande);
                $commandeForm->handleRequest($request);
                foreach( $produits as $prd ) {
                    $prd->setQuantite($request->request->get('prod'.(string)$prd->getId(),0));

                }
                return $this->render('commande/index.html.twig',[ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
                    "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde, 'miseEnAvant'=>$miseEnAvant,  "dateTodey"=>$todey,

                ]);
            }
            //**************erreur si livraison sup à 1mois

            if ($diff>31) {
                //************toutes les miseENavant apres la date du jour
                $todey=$commande->getJourDeLivraison();//ligneajoutee
                $todayTrente = new \DateTime('now');
                $todayTrente->add(new DateInterval('P30D'));
                try{
                    $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                    $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
                }catch (\Doctrine\DBAL\Exception $e)
                {
                    $this->addFlash('error', 'Problème d\'accès àla base de données '.'commandecontroller248');
                    return $this->redirectToRoute('home_connected', [ ]);
                }

                //******************************************
                $this->addFlash('error', "Date de livraison >à 1mois.");
                $commandeForm = $this->createForm(CommandeType::class, $commande);
                $commandeForm->handleRequest($request);
                foreach( $produits as $prd ) {
                    $prd->setQuantite($request->request->get('prod'.(string)$prd->getId(),0));

                }
                return $this->render('commande/index.html.twig',[ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
                    "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde, 'miseEnAvant'=>$miseEnAvant,  "dateTodey"=>$todey,

                ]);
            }
            //*********erreur si livraison j+1 et apres 11h
            if ($diff==1 and  intval($heure)>10) {
                //************toutes les miseENavant apres la date du jour
                $todey=$commande->getJourDeLivraison();//ligneajoutee
                $todayTrente = new \DateTime('now');
                $todayTrente->add(new DateInterval('P30D'));
                try{
                    $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                    $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
                } catch (\Doctrine\DBAL\Exception $e)
                {
                    $this->addFlash('error', 'Problème d\'accès àla base de données '.'commandecontroller269');
                    return $this->redirectToRoute('home_connected', [ ]);
                }

                //******************************************
                $this->addFlash('error', "heure dépassée pour livraison le lendemain (avant 11h)");
                $commandeForm = $this->createForm(CommandeType::class, $commande);
                $commandeForm->handleRequest($request);
                foreach( $produits as $prd ) {
                    $prd->setQuantite($request->request->get('prod'.(string)$prd->getId(),0));

                }
                return $this->render('commande/index.html.twig',[ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
                    "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde, 'miseEnAvant'=>$miseEnAvant,  "dateTodey"=>$todey,

                ]);
            }
            //********erreur de date inf à la date du jour
            if (intval($dttjour)>=intval($jour) and intval($dttmois)==intval($mois) and intval($dttan)==intval($annee) ) {
                //************toutes les miseENavant apres la date du jour
                $todey=$commande->getJourDeLivraison();//ligneajoutee
                $todayTrente = new \DateTime('now');
                $todayTrente->add(new DateInterval('P30D'));
                try {
                    $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                    $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
                } catch (\Doctrine\DBAL\Exception $e)
                {
                    $this->addFlash('error', 'Problème d\'accès àla base de données '.'commandecontroller298');
                    return $this->redirectToRoute('home_connected', [ ]);
                }
                //******************************************
                $this->addFlash('error', "Problème de date de livraison.");
                $commandeForm = $this->createForm(CommandeType::class, $commande);
                $commandeForm->handleRequest($request);
                foreach( $produits as $prd ) {
                    $prd->setQuantite($request->request->get('prod'.(string)$prd->getId(),0));

                }
                return $this->render('commande/index.html.twig',[ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
                    "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde, 'miseEnAvant'=>$miseEnAvant,  "dateTodey"=>$todey,

                ]);
            }
            if (intval($dttmois)>intval($mois) and intval($dttan)==intval($annee) ) {
                //************toutes les miseENavant apres la date du jour
                $todey=$commande->getJourDeLivraison();//ligneajoutee
                $todayTrente = new \DateTime('now');
                $todayTrente->add(new DateInterval('P30D'));
                try {
                    $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                    $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
                } catch (\Doctrine\DBAL\Exception $e)
                {
                    $this->addFlash('error', 'Problème d\'accès àla base de données '.'commandecontroller325');
                    return $this->redirectToRoute('home_connected', [ ]);
                }
                //******************************************
                $this->addFlash('error', "Problème de date de livraison.");
                $commandeForm = $this->createForm(CommandeType::class, $commande);
                $commandeForm->handleRequest($request);
                foreach( $produits as $prd ) {
                    $prd->setQuantite($request->request->get('prod'.(string)$prd->getId(),0));

                }
                return $this->render('commande/index.html.twig',[ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
                    "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde, 'miseEnAvant'=>$miseEnAvant,  "dateTodey"=>$todey,

                ]);
            }
            if (intval($dttan)>intval($annee) ) {
                //************toutes les miseENavant apres la date du jour
                $todey=$commande->getJourDeLivraison();//ligneajoutee
                $todayTrente = new \DateTime('now');
                $todayTrente->add(new DateInterval('P30D'));
                try{
                    $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                    $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
                } catch (\Doctrine\DBAL\Exception $e)
                {
                    $this->addFlash('error', 'Problème d\'accès àla base de données '.'commandecontroller352');
                    return $this->redirectToRoute('home_connected', [ ]);
                }
                //******************************************
                $this->addFlash('error', "Problème de date de livraison.");
                $commandeForm = $this->createForm(CommandeType::class, $commande);
                $commandeForm->handleRequest($request);
                foreach( $produits as $prd ) {
                    $prd->setQuantite($request->request->get('prod'.(string)$prd->getId(),0));

                }
                return $this->render('commande/index.html.twig',[ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
                    "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde, 'miseEnAvant'=>$miseEnAvant,  "dateTodey"=>$todey,

                ]);
            }



            // recupere toutes les produits
            try {
                $produitRepo = $this->getDoctrine()->getRepository(Produit::class);
                $produits = $produitRepo->findAll();
            } catch (\Doctrine\DBAL\Exception $e)
            {
                $this->addFlash('error', 'Problème d\'accès àla base de données '.'commandecontroller378');
                return $this->redirectToRoute('home_connected', [ ]);
            }
            foreach( $produits as $prd ) {//recupere les valeurs des input en formulaire pour les produits
                $prd->setQuantite($request->request->get('prod'.(string)$prd->getId(),0));
                //voir si tout produit quantite egal à 0 pour ne pas enregistrer
                //$commande->add($prd);
                //$prd->addcommande($commande);
                // recupere l etat envoyee
                try {
                    $etatRepo = $this->getDoctrine()->getRepository(EtatCommande::class);
                    $etat = $etatRepo->find(2);
                } catch (\Doctrine\DBAL\Exception $e)
                {
                    $this->addFlash('error', 'Problème d\'accès àla base de données '.'commandecontroller393');
                    return $this->redirectToRoute('home_connected', [ ]);
                }
                $commande->setEtatCommande($etat);
                $commande->setObject($produits);
                //$em->persist($prd); pâs d'enregistrement de la quantite ds l'entity produit (doit toujours etre egal à zero)
            }
            $pasVide=false;//****voir si cde vide en quantite
            foreach ($commande->getObject() as $lobjet)
            {
                if ($lobjet->getQuantite()>0){$pasVide=true;}
            }
            //*****si cde vide retour en page de cde
            if ( $pasVide==false ) {
                //************toutes les miseENavant apres la date du jour
                $todey=$commande->getJourDeLivraison();//ligneajoutee
                $todayTrente = new \DateTime('now');
                $todayTrente->add(new DateInterval('P30D'));
                try {
                    $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                    $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
                } catch (\Doctrine\DBAL\Exception $e)
                {
                    $this->addFlash('error', 'Problème d\'accès àla base de données '.'commandecontroller417');
                    return $this->redirectToRoute('home_connected', [ ]);
                }
                //****************************************

                //******************************************
                $this->addFlash('error', "La commande est vide.");
                $commande->setNote("message à remplir juste avant envoi (impératif)");//*******ligne ajout
                $commandeForm = $this->createForm(CommandeType::class, $commande);
                $commandeForm->handleRequest($request);
                foreach( $produits as $prd ) {
                    $typeProd=$request->request->get('prod'.(string)$prd->getId(),0);
                    if (is_integer($typeProd)) {
                        $prd->setQuantite($request->request->get('prod' . (string)$prd->getId(), 0));
                    }
                }
                return $this->render('commande/index.html.twig',[ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
                    "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde, 'miseEnAvant'=>$miseEnAvant,  "dateTodey"=>$todey,

                ]);
            }
            //****************************
            //*********controle securité si user connecté est egal à l auteur de la commande
            $utilisateur=$commande->getUtilisateur();
            $numUtilisateur=$utilisateur->getTelephoneSociete();
            $nomUtilisateur=$utilisateur->getNomDeLaSociete();
            $nameUtilisateur=$utilisateur->getNom();
            if ($idUtilisateur=$user->getId()){

                //**************************
                //***on enregistre la cde
                try {
                    $em->persist($commande);
                    $em->flush();
                    //**************mettre à jour listingCommande ds Utilisateur************************************************
                    // récupérer l utilisateur à modifier $personne (celui qui a qui la cde appartient) $cde passé
                    $personne=$commande->getUtilisateur();
                    $utiliRepo = $this->getDoctrine()->getRepository(Utilisateur::class);
                    $utili = $utiliRepo->find($personne);
                    $utili->addCommande($commande);
                    $em->persist($utili);
                    $em->flush();
                    //envoi mail
                    $email = (new TemplatedEmail())
                        ->from('contact@seatrader.eu')
                        ->to('contact@seatrader.eu')
                        //->cc('cc@example.com')
                        //->bcc('bcc@example.com')
                        //->replyTo('fabien@example.com')
                        ->priority(Email::PRIORITY_HIGH)
                        ->subject('commande reçue')
                        ->text('message du site seatrader-appli: une nouvelle cde de '.$nomUtilisateur.
                            ' tel:'.$numUtilisateur.' nom: '.$nameUtilisateur)
                        ->htmlTemplate( 'mail/mail.html.twig');

                    $mailer->send($email);
                    //******************************************************************************************************

                    //***********redirection vers voir mes commandes
                    $this->addFlash('success', "Commande enregistrée et envoyée");

                    return $this->redirectToRoute('voir_cde');
                } catch (\Doctrine\DBAL\Exception $e)
                {
                    $this->addFlash('error', 'Nous n\'avons pas pu enregistrer la cde '.'commandecontroller482');
                    return $this->redirectToRoute('home_connected', [ ]);
                }

            } else {
                $this->addFlash('error', "Un problème a été détecté. La commande n'a pas été enregistré.");
            }
        }


        return $this->render('commande/index.html.twig', [ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
            "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde, 'miseEnAvant'=>$miseEnAvant,  "dateTodey"=>$todey,
        ]);
    }

    //*****************fonctionnalité voir/AFFICHER mes cdes pour un utilisateur connecté
    //***************la route /monAppli/ est accessible uniquement au ROLE_USER OU ROLE_ADMIN
    /**
     * @Route("/monAppli/commande/cde", name="voir_cde")
     */
    public function voirCde() :Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        // on récupère l'user
        $user=$this->getUser();
        //DATE DU  JOUR
        $today = new \DateTime('now');
        $commandeRepo = $this->getDoctrine()->getRepository(Commande::class);
        try {
            //filter cde avec date livraison apres date du jour -l'utilisateur a encore du temps (au moins 24h) sur FOND VERT
            $commandeApresJour = $commandeRepo->filtrerLesCdes($user);
            //FILTRER VIEILLE CDE  recuperer les cdee avant la date du jour et -30jours D'ANCIENNETE FOND GRIS
            $commandeAvantJour = $commandeRepo->filtrerLesAnciennesCdes($user);
            // FILTRER recupere les commandes +1j ou apres date du jour modifiable encore jusqu a 11h du matin en  FOND ORANGE
            $commandeEncoreModifiable = $commandeRepo->filtrerLesCdesEgalToday($user);
            // recupere les commandes +1j apres date du jour non modifiable car heure>11 afficher en FOND GRIS
            $commandeNonModifiable = $commandeRepo->filtrerLesCdesNonEgalToday($user);
        } catch (\Doctrine\DBAL\Exception $e)
        {
            $this->addFlash('error', 'Problème d\'accès à la base de données'.'commandecontroller522');
            return $this->redirectToRoute('home_connected', [ ]);
        }
        //***************************
        return $this->render('commande/voir_cde.html.twig', [ "dateToday"=>$today,"user"=>$user,
            "commandeApresJour"=>$commandeApresJour,  "commandeAvantJour"=>$commandeAvantJour,
            'commandeEncoreModifiable'=>$commandeEncoreModifiable,
            'commandeNonModifiable'=>$commandeNonModifiable,
        ]);
    }
     /**
     * @Route("/monAppli/commande-/{id}", name="supprimer-cde")
     */
    public function supprimerCde($id, EntityManagerInterface $em){//********************supprimer une commande
        $this->denyAccessUnlessGranted('ROLE_USER');
        //****************on recupere la cde
        try {
        $cdeRepo = $this->getDoctrine()->getRepository(Commande::class);
        $cde = $cdeRepo->find($id);
        } catch (\Doctrine\DBAL\Exception $e)
        {
            $this->addFlash('error', 'Problème d\'accès à la base de données: '.'commandecontroller544');
            return $this->redirectToRoute('home_connected', [ ]);
        }

        if ($cde==null){ //si $commande null déconnexion
            return $this->redirectToRoute('app_logout');
        }
        $personne=$cde->getUtilisateur();
        // on récupère l'user
        $user=$this->getUser();
        $role=$user->getRoles();
        //****securité verifier que la cde appartient bien à l'utilisateur connectée (client) sinon deconnexion
        If ($personne!=$user and $role==['ROLE_USER']) {
            return $this->redirectToRoute('app_logout');
        }
        try {
        $em->remove($cde);
        $em->flush();
        return $this->redirectToRoute('home_connected--user');
        } catch (\Doctrine\DBAL\Exception $e)
        {
            $this->addFlash('error', 'Erreur lors de la suppression : Nous n\' avons pas pu supprimer la commande. Contactez l administrateur'.' commandecontroller566');
            return $this->redirectToRoute('home_connected');
        }
    }


    //********************supprimer cde
    /**
     * @Route("/monAppli/commande-modifier/{id}", name="modifier-cde")
     */
    public function modifierCde($id, Request $request, EntityManagerInterface $em,   MailerInterface $mailer)
    {    // on récupère l'user/ date today
        $user=$this->getUser();
        $role=$user->getRoles();
        $today = new \DateTime('now');
        //****************on recupere la cde
        try {
            $cdeRepo = $this->getDoctrine()->getRepository(Commande::class);
            $commande = $cdeRepo->find($id);
        } catch (\Doctrine\DBAL\Exception $e)
        {
            $this->addFlash('error', 'Problème d\'accès à la base de données:'.'commandecontroller588');
            return $this->redirectToRoute('home_connected');
        }
        //****securité verifier que la cde appartient bien à l'utilisateur connectée (client) sinon deconnexion
        if ($commande==null){ //si $commande null déconnexion
            return $this->redirectToRoute('app_logout');
        }
        $personne=$commande->getUtilisateur();
        // on récupère l'user
        If ($personne!=$user and $role==['ROLE_USER']) {
            return $this->redirectToRoute('app_logout');
        }
        //*************************************************************************************
        // recupere toutes les familles
        try {
        $familleProduitRepo = $this->getDoctrine()->getRepository(FamilleProduit::class);
        $familleProduit = $familleProduitRepo->findAll();
        } catch (\Doctrine\DBAL\Exception $e)
        {
            $this->addFlash('error', 'Problème d\'accès à la base de données:'.'commandecontroller608');
            return $this->redirectToRoute('home_connected');
        }
        //***CDE
        $cde=new Commande();

        //**********************on recup les produits de la cde
        $produits=$commande->getObject();
        $this->addFlash('error', "Vous pouvez modifier cette commande.");
        $commandeForm = $this->createForm(CommandeType::class, $commande);
        $commandeForm->handleRequest($request);
        //************toutes les miseENavant apres la date du jour

        $todey=$commande->getJourDeLivraison();
        $todayTrente = new \DateTime('now');
        $todayTrente->add(new DateInterval('P30D'));
        try {
            $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
            $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
        } catch (\Doctrine\DBAL\Exception $e)
        {
            $this->addFlash('error', 'Problème d\'accès à la base de données:'.'commandecontroller630');
            return $this->redirectToRoute('home_connected');
        }
        //******************************************
        //***********************************soumission du formulaire
        if ($commandeForm->isSubmitted() and $commandeForm->isValid()) {
            $user=$this->getUser();
            $role=$user->getRoles();
            //*******verif des dates et ouverture des cde
            $dateVoulue=($commande->getJourDeLivraison());
            $dateVoulue2=($commande->getJourDeLivraison());

            //******************** jour en francais
            $dtt=date_format($dateVoulue,"d/m/Y");
            $dtt2=date_format($dateVoulue2,"Y-m-d");

            //$dtt=$dateVoulue;
            // tableau des jours de la semaine date_format($date,"Y/m/d H:i:s");
            //$dtt=date("d/m/Y");
            //********connaitre le jour de la semaine
            $joursem = array('dim', 'lun', 'mar', 'mer', 'jeu', 'ven', 'sam');
            // extraction des jour, mois, an de la date
            list($jour, $mois, $annee) = explode('/', $dtt);
            // calcul du timestamp
            $timestamp = mktime (0, 0, 0, $mois, $jour, $annee);
            // affichage du jour de la semaine
            $jourSem= $joursem[date("w",$timestamp)];
            //***************si message non changé
            if ( $commande->getNote()=="message à remplir juste avant de clicker sur enregister et envoyer (impératif)." ) {
                //************toutes les miseENavant apres la date du jour
                $todey=$commande->getJourDeLivraison();//ligneajoutee
                $todayTrente = new \DateTime('now');
                $todayTrente->add(new DateInterval('P30D'));
                try {
                    $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                    $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
                } catch (\Doctrine\DBAL\Exception $e)
                {
                    $this->addFlash('error', 'Problème d\'accès à la base de données:'.'commandecontroller669');
                    return $this->redirectToRoute('home_connected');
                }
                //******************************************
                $this->addFlash('error', "commande en cours (note à changer juste avant l'envoi de la commande (impératif))");
                $commandeForm = $this->createForm(CommandeType::class, $commande);
                $commandeForm->handleRequest($request);
                foreach( $produits as $prd ) {
                    $prd->setQuantite($request->request->get('prod'.(string)$prd->getId(),0));

                }
                return $this->render('commande/index.html.twig',[ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
                    "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde, 'miseEnAvant'=>$miseEnAvant,  "dateTodey"=>$todey,

                ]);
            }
            //*********si jour livraison egale à dim ou lundi pas de cde possible
            //******************************
            //** MANQUE VOIR SI DATE OUVERTE(pour dim/et lun) ou bloqué(pour mar/mer/jeu/ven/sam) PAR ADMIN
            //** MANQUE VOIR SI DATE OUVERTE(pour dim/et lun) ou bloqué(pour mar/mer/jeu/ven/sam) PAR ADMIN
            $calendrierLivRepo = $this->getDoctrine()->getRepository(CalendrierLivraison::class);
            $blocCalendrierLiv=$calendrierLivRepo->filtreDateBloc($dtt2);
            $openCalendrierLiv=$calendrierLivRepo->filtreDateOpen($dtt2);
            //******************************
            if (($jourSem=="dim" or $jourSem=="lun" or sizeof($blocCalendrierLiv)>0) and sizeof($openCalendrierLiv)<1 and $role!=['ROLE_ADMIN','ROLE_USER']) {
            //if ($jourSem=="dim" or $jourSem=="lun" and $role!=['ROLE_ADMIN','ROLE_USER']) {
                //************toutes les miseENavant apres la date du jour
                $todey=$commande->getJourDeLivraison();//ligneajoutee
                $todayTrente = new \DateTime('now');
                $todayTrente->add(new DateInterval('P30D'));
                try {
                    $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                    $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
                } catch (\Doctrine\DBAL\Exception $e)
                {
                    $this->addFlash('error', 'Problème d\'accès à la base de données:'.'commandecontroller705');
                    return $this->redirectToRoute('home_connected');
                }
                //******************************************
                $this->addFlash('error', "La livraison n'est pas ouverte pour ce jour là.");
                $commandeForm = $this->createForm(CommandeType::class, $commande);
                $commandeForm->handleRequest($request);
                foreach( $produits as $prd ) {
                    $prd->setQuantite($request->request->get('prod'.(string)$prd->getId(),0));

                }
                return $this->render('commande/index.html.twig',[ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
                    "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde, 'miseEnAvant'=>$miseEnAvant,  "dateTodey"=>$todey,

                ]);
            }
            //**********controle si date du jour <24h date de liv
            $dttjour=date("d");
            $dttmois=date("m");
            $dttan=date("Y");
            $dtjour2=new \DateTime('now');
            $dtjour2=date_format($dtjour2,"Y-m-d");
            //$diff = round((strtotime($dtt) - strtotime($dtjour))/(60*60*24)-1);
            $heure=date('H');

            //calcul de la diff
            // jour mois annee date de liv choisie
            $start = new DateTimeImmutable($dtjour2);//date de départ
            $end = new DateTimeImmutable($dtt2);//date de départ
            $interval = $start->diff($end);//on récupère la différence entre ces 2 dates
            $diff=$interval->format('%a');//affiche : en jours
            if(intval($jour)<intval($dttjour) and intval($mois)==intval($dttmois) and intval($annee)==intval($dttan) and $role!=['ROLE_ADMIN']){
                $diff=$diff*-1;
            } else  if( intval($mois)<intval($dttmois) and intval($annee)==intval($dttan)) { $diff=$diff*-1;}
            else  if( intval($annee)<intval($dttan)) { $diff=$diff*-1;}
            /*var_dump($dttjour);
            var_dump($dttmois);
            var_dump($dttan);
            var_dump($dtjour);
            var_dump($diff);
            var_dump($jourSem);
            var_dump($jour);
            var_dump($mois);
            var_dump($annee);
            var_dump($heure);
            die();*/
            //*******************************
            if ($diff<=0 and $role!=['ROLE_ADMIN','ROLE_USER']) {
                //************toutes les miseENavant apres la date du jour
                $todey=$commande->getJourDeLivraison();//ligneajoutee
                $todayTrente = new \DateTime('now');
                $todayTrente->add(new DateInterval('P30D'));
                try {
                    $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                    $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
                } catch (\Doctrine\DBAL\Exception $e)
                {
                    $this->addFlash('error', 'Problème d\'accès à la base de données:'.'commandecontroller764');
                    return $this->redirectToRoute('home_connected');
                }
                //******************************************
                $this->addFlash('error', "Erreur Date de livraison.");

                $commandeForm = $this->createForm(CommandeType::class, $commande);
                $commandeForm->handleRequest($request);
                foreach( $produits as $prd ) {
                    $prd->setQuantite($request->request->get('prod'.(string)$prd->getId(),0));

                }
                return $this->render('commande/index.html.twig',[ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
                    "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde, 'miseEnAvant'=>$miseEnAvant,  "dateTodey"=>$todey,

                ]);
            }
            //**************erreur si livraison sup à 1mois

            if ($diff>31) {
                //************toutes les miseENavant apres la date du jour
                $todey=$commande->getJourDeLivraison();//ligneajoutee
                $todayTrente = new \DateTime('now');
                $todayTrente->add(new DateInterval('P30D'));
                try {
                    $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                    $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
                } catch (\Doctrine\DBAL\Exception $e)
                {
                    $this->addFlash('error', 'Problème d\'accès à la base de données:'.'commandecontroller794');
                    return $this->redirectToRoute('home_connected');
                }
                //******************************************
                $this->addFlash('error', "Date de livraison >à 1mois.");
                $commandeForm = $this->createForm(CommandeType::class, $commande);
                $commandeForm->handleRequest($request);
                foreach( $produits as $prd ) {
                    $prd->setQuantite($request->request->get('prod'.(string)$prd->getId(),0));

                }
                return $this->render('commande/index.html.twig',[ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
                    "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde, 'miseEnAvant'=>$miseEnAvant,  "dateTodey"=>$todey,

                ]);
            }
            //*********erreur si livraison j+1 et apres 11h
            if (($diff==1 and  intval($heure)>10) and $role!=['ROLE_ADMIN','ROLE_USER']) {

                //************toutes les miseENavant apres la date du jour
                $todey=$commande->getJourDeLivraison();//ligneajoutee
                $todayTrente = new \DateTime('now');
                $todayTrente->add(new DateInterval('P30D'));
                try {
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
                } catch (\Doctrine\DBAL\Exception $e)
                {
                    $this->addFlash('error', 'Problème d\'accès à la base de données:'.'commandecontroller823');
                    return $this->redirectToRoute('home_connected');
                }
                //******************************************
                $this->addFlash('error', "heure dépassée pour livraison le lendemain (avant 11h)");
                $commandeForm = $this->createForm(CommandeType::class, $commande);
                $commandeForm->handleRequest($request);
                foreach( $produits as $prd ) {
                    $prd->setQuantite($request->request->get('prod'.(string)$prd->getId(),0));

                }
                return $this->render('commande/index.html.twig',[ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
                    "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde, 'miseEnAvant'=>$miseEnAvant,  "dateTodey"=>$todey,

                ]);
            }
            //********erreur de date inf à la date du jour
            if (intval($dttjour)>=intval($jour) and intval($dttmois)==intval($mois) and intval($dttan)==intval($annee) and $role!=['ROLE_ADMIN','ROLE_USER']) {
                //************toutes les miseENavant apres la date du jour
                $todey=$commande->getJourDeLivraison();//ligneajoutee
                $todayTrente = new \DateTime('now');
                $todayTrente->add(new DateInterval('P30D'));
                try {
                    $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                    $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
                } catch (\Doctrine\DBAL\Exception $e)
                {
                    $this->addFlash('error', 'Problème d\'accès à la base de données:'.'commandecontroller851');
                    return $this->redirectToRoute('home_connected');
                }
                //******************************************
                $this->addFlash('error', "Problème de date de livraison.");
                $commandeForm = $this->createForm(CommandeType::class, $commande);
                $commandeForm->handleRequest($request);
                foreach( $produits as $prd ) {
                    $prd->setQuantite($request->request->get('prod'.(string)$prd->getId(),0));

                }
                return $this->render('commande/index.html.twig',[ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
                    "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde, 'miseEnAvant'=>$miseEnAvant,  "dateTodey"=>$todey,

                ]);
            }
            if (intval($dttmois)>intval($mois) and intval($dttan)==intval($annee) and $role!=['ROLE_ADMIN','ROLE_USER']) {
                //************toutes les miseENavant apres la date du jour
                $todey=$commande->getJourDeLivraison();//ligneajoutee
                $todayTrente = new \DateTime('now');
                $todayTrente->add(new DateInterval('P30D'));
                try {
                    $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                    $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
                } catch (\Doctrine\DBAL\Exception $e)
                {
                    $this->addFlash('error', 'Problème d\'accès à la base de données:'.'commandecontroller878');
                    return $this->redirectToRoute('home_connected');
                }
                //******************************************
                $this->addFlash('error', "Problème de date de livraison.");
                $commandeForm = $this->createForm(CommandeType::class, $commande);
                $commandeForm->handleRequest($request);
                foreach( $produits as $prd ) {
                    $prd->setQuantite($request->request->get('prod'.(string)$prd->getId(),0));

                }
                return $this->render('commande/index.html.twig',[ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
                    "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde, 'miseEnAvant'=>$miseEnAvant,  "dateTodey"=>$todey,

                ]);
            }
            if (intval($dttan)>intval($annee) and $role!=['ROLE_ADMIN','ROLE_USER']) {
                //************toutes les miseENavant apres la date du jour
                $todey=$commande->getJourDeLivraison();//ligneajoutee
                $todayTrente = new \DateTime('now');
                $todayTrente->add(new DateInterval('P30D'));
                try {
                    $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                    $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
                } catch (\Doctrine\DBAL\Exception $e)
                {
                    $this->addFlash('error', 'Problème d\'accès à la base de données:'.'commandecontroller905');
                    return $this->redirectToRoute('home_connected');
                }
                //******************************************
                $this->addFlash('error', "Problème de date de livraison.");
                $commandeForm = $this->createForm(CommandeType::class, $commande);
                $commandeForm->handleRequest($request);
                foreach( $produits as $prd ) {
                    $prd->setQuantite($request->request->get('prod'.(string)$prd->getId(),0));

                }
                return $this->render('commande/index.html.twig',[ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
                    "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde, 'miseEnAvant'=>$miseEnAvant,  "dateTodey"=>$todey,

                ]);
            }



            // recupere toutes les produits
            try {
            $produitRepo = $this->getDoctrine()->getRepository(Produit::class);
            $produits = $produitRepo->findAll();
            } catch (\Doctrine\DBAL\Exception $e)
            {
                $this->addFlash('error', 'Problème d\'accès à la base de données:'.'commandecontroller931');
                return $this->redirectToRoute('home_connected');
            }

            foreach( $produits as $prd ) {//recupere les valeurs des input en formulaire pour les produits
                $prd->setQuantite($request->request->get('prod'.(string)$prd->getId(),0));
                //voir si tout produit quantite egal à 0 pour ne pas enregistrer
                //$commande->add($prd);
                //$prd->addcommande($commande);
                // recupere l etat envoyee
                $etatRepo = $this->getDoctrine()->getRepository(EtatCommande::class);
                $etat = $etatRepo->find(2);

                $commande->setEtatCommande($etat);
                $commande->setObject($produits);
                //$em->persist($prd); pâs d'enregistrement de la quantite ds l'entity produit (doit toujours etre egal à zero)
            }
            $pasVide=false;//****voir si cde vide en quantite
            foreach ($commande->getObject() as $lobjet)
            {
                if ($lobjet->getQuantite()>0){$pasVide=true;}
            }
            //*****si cde vide retour en page de cde
            if ( $pasVide==false and $role!=['ROLE_ADMIN','ROLE_USER']) {
                //************toutes les miseENavant apres la date du jour
                $todey=$commande->getJourDeLivraison();//ligneajoutee
                $todayTrente = new \DateTime('now');
                $todayTrente->add(new DateInterval('P30D'));
                try {
                    $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                    $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
                } catch (\Doctrine\DBAL\Exception $e)
                {
                    $this->addFlash('error', 'Problème d\'accès à la base de données:'.'commandecontroller965');
                    return $this->redirectToRoute('home_connected');
                }
                //******************************************
                $this->addFlash('error', "La commande est vide.");
                $commandeForm = $this->createForm(CommandeType::class, $commande);
                $commandeForm->handleRequest($request);
                foreach( $produits as $prd ) {
                    $prd->setQuantite($request->request->get('prod'.(string)$prd->getId(),0));

                }
                return $this->render('commande/index.html.twig',[ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
                    "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde, 'miseEnAvant'=>$miseEnAvant,  "dateTodey"=>$todey,

                ]);
            }
            //****************************
            //*********controle securité si user connecté est egal à l auteur de la commande
            $utilisateur=$commande->getUtilisateur();
            $idUtilisateur=$utilisateur->getId();
            $utilisateur=$commande->getUtilisateur();
            $numUtilisateur=$utilisateur->getTelephoneSociete();
            $nomUtilisateur=$utilisateur->getNomDeLaSociete();
            $nameUtilisateur=$utilisateur->getNom();
            $role=$user->getRoles();
            if ( $idUtilisateur==$user->getId() or $role==['ROLE_ADMIN','ROLE_USER']){

                //**************************
                //***on enregistre la cde
                try {
                    $em->persist($commande);
                    $em->flush();
                    //envoi mail
                    $email = (new TemplatedEmail())
                        ->from('contact@seatrader.eu')
                        ->to('contact@seatrader.eu')
                        //->cc('cc@example.com')
                        //->bcc('bcc@example.com')
                        //->replyTo('fabien@example.com')
                        ->priority(Email::PRIORITY_HIGH)
                        ->subject('commande modifiée reçue')
                        ->text('message du site seatrader-appli: une MODIFICATION cde de '.$nomUtilisateur.
                            ' tel:'.$numUtilisateur.' nom: '.$nameUtilisateur)
                        ->htmlTemplate( 'mail/mail.html.twig');

                    $mailer->send($email);
                } catch (\Doctrine\DBAL\Exception $e)
                {
                    $this->addFlash('error', 'Nous n\'avons pas pu enregistrer la cde:'.'commandecontroller1014');
                    return $this->redirectToRoute('home_connected');
                }


                //***********redirection vers voir mes commandes
                $this->addFlash('success', "Commande enregistrée et envoyée");
                if ($role==['ROLE_ADMIN','ROLE_USER']){
                    return $this->redirectToRoute('home_connected');
                }
                return $this->redirectToRoute('voir_cde');

            } else {
                $this->addFlash('error', "Un problème a été détecté. La commande n'a pas été enregistré.");
            }
        }

        //***********************************
        return $this->render('commande/index.html.twig',[ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
            "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$commande, 'miseEnAvant'=>$miseEnAvant,  "dateTodey"=>$todey,

        ]);


    }



    //********************voir les cde envoyee/recept/traité/archivé pour admin
    /**
     * @Route("/admin/commandes/{statut}", name="voir-cde-admin")
     */
    public function voirCdeAdmin($statut)
    {    // on récupère l'user/ date today
        $user=$this->getUser();
        $today = new \DateTime('now');
        try {
            $cdeRepo = $this->getDoctrine()->getRepository(Commande::class);
            if ($statut == "commandesEnvoyee") {
                $etat = 2;
                $cde = $cdeRepo->filtreCdeStatut($etat);
            } elseif ($statut == "commandesReceptionnee") {
                $etat = 3;
                $cde = $cdeRepo->filtreCdeStatut($etat);
            } elseif ($statut == "commandesTraitee") {
                $etat = 4;
                $cde = $cdeRepo->filtreCdeStatut($etat);
            } elseif ($statut == "commandesArchivee") {
                $etat = 1;
                $cde = $cdeRepo->filtreCdeStatut($etat);
            }
        } catch (\Doctrine\DBAL\Exception $e)
        {
            $this->addFlash('error', 'Problème d\'accès à la base de données:'.'commandecontroller1068');
            return $this->redirectToRoute('home_connected');
        }
        //***********************************
        return $this->render('commande/voir-cde-admin.html.twig',[ "dateToday"=>$today,"user"=>$user,
            'cde'=>$cde, 'statut'=>$statut,

        ]);


    }
    //**************************************************************
    //********************
    /**
     * @Route("/admin/voir-cde-admin/{id}", name="voir-une-cde-admin")
     */
    public function voirUneCdeAdmin($id, EntityManagerInterface $em, Request $request): Response
    {
        // recupere toutes les familles/produits
        try {
            $familleProduitRepo = $this->getDoctrine()->getRepository(FamilleProduit::class);
            $familleProduit = $familleProduitRepo->findAll();
            // recupere toutes l etat 3 (
            $etatCdeRepo = $this->getDoctrine()->getRepository(EtatCommande::class);
            $etat = $etatCdeRepo->find(3);
            //****************on recupere la cde
            //********ajouter controle user est le bon ou admin à faire
            $cdeRepo = $this->getDoctrine()->getRepository(Commande::class);
            $cde = $cdeRepo->find($id);
        } catch (\Doctrine\DBAL\Exception $e)
        {
            $this->addFlash('error', 'Problème d\'accès à la base de données:'.'commandecontroller1100');
            return $this->redirectToRoute('home_connected');
        }
        //********on change le statut de la cde
        $etatCde=$cde->getEtatCommande();
        if ($etatCde=="envoyée")
        {
        $cde->setEtatCommande($etat);
            //***on enregistre la cde
            try {
                $em->persist($cde);
                $em->flush();
            } catch (\Doctrine\DBAL\Exception $e)
            {
                $this->addFlash('error', 'Nous n\'avons pas pu changer l\'état:'.'commandecontroller1115');
                return $this->redirectToRoute('home_connected');
            }
        }

        // on récupère l'user
        $user=$this->getUser();
        $today = new \DateTime('now');
        //*****on cree formulaire pour etatcommande

        $etatCmmdeForm = $this->createForm(CommandeEtatType::class, $cde);
        $etatCmmdeForm->handleRequest($request);
        //**************************
        if ($etatCmmdeForm->isSubmitted() and $etatCmmdeForm->isValid()) {

            //***on enregistre la cde
            try {
                $em->persist($cde);
                $em->flush();
            } catch (\Doctrine\DBAL\Exception $e)
            {
                $this->addFlash('error', 'Nous n\'avons pas pu changer l\'état:'.' commandecontroller1137');
                return $this->redirectToRoute('home_connected');
            }
            return $this->redirectToRoute('home_connected');

        }
        //********************route
        return $this->render('commande/voir-une-cde-admin.html.twig',[ "dateToday"=>$today,"user"=>$user,
            'cde'=>$cde, 'familleProduit'=>$familleProduit, 'etatCmmdeForm'=>$etatCmmdeForm->createView(),

        ]);

    }
}
