<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\EtatCommande;
use App\Entity\EtatUtilisateur;
use App\Entity\FamilleProduit;
use App\Entity\MiseEnAvant;
use App\Entity\Produit;
use App\Form\CommandeType;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
{
    /**
     * @Route("/commande", name="faire_cde")
     */
    public function index(EntityManagerInterface $em, Request $request): Response
    {

        // recupere toutes les familles/produits
        $familleProduitRepo = $this->getDoctrine()->getRepository(FamilleProduit::class);
        $familleProduit = $familleProduitRepo->findAll();
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
        $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
        $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
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
            if ( $commande->getNote()=="message à remplir juste avant envoi (impératif)" ) {
                //************toutes les miseENavant apres la date du jour
                $todey=$commande->getJourDeLivraison();//ligneajoutee
                $todayTrente = new \DateTime('now');
                $todayTrente->add(new DateInterval('P30D'));
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
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
            //******************************
            if ($jourSem=="dim" or $jourSem=="lun") {
                //************toutes les miseENavant apres la date du jour
                $todey=$commande->getJourDeLivraison();//ligneajoutee
                $todayTrente = new \DateTime('now');
                $todayTrente->add(new DateInterval('P30D'));
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
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
            $dtjour=date("d/m/Y");
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
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
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
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
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
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
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
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
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
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
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
            $produitRepo = $this->getDoctrine()->getRepository(Produit::class);
            $produits = $produitRepo->findAll();

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
            if ( $pasVide==false ) {
                //************toutes les miseENavant apres la date du jour
                $todey=$commande->getJourDeLivraison();//ligneajoutee
                $todayTrente = new \DateTime('now');
                $todayTrente->add(new DateInterval('P30D'));
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
                //******************************************
                $this->addFlash('error', "La commande est vide.");
                $commande->setNote("message à remplir juste avant envoi (impératif)");//*******ligne ajout
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
            if ($idUtilisateur=$user->getId()){

                //**************************
                //***on enregistre la cde
                $em->persist($commande);
                $em->flush();


                //***********redirection vers voir mes commandes
                $this->addFlash('success', "Commande enregistrée et envoyée");

                return $this->redirectToRoute('voir_cde');

            } else {
                $this->addFlash('error', "Un problème a été détecté. La commande n'a pas été enregistré.");
            }
        }


        return $this->render('commande/index.html.twig', [ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
            "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde, 'miseEnAvant'=>$miseEnAvant,  "dateTodey"=>$todey,
        ]);
    }

    //*****************voir mes cdes
    /**
     * @Route("/commande/cde", name="voir_cde")
     */
    public function voirCde() :Response
    {

        // on récupère l'user
        $user=$this->getUser();
        $today = new \DateTime('now');
        // recupere les commandes +2j apres date du jour
        $commandeRepo = $this->getDoctrine()->getRepository(Commande::class);
        $commandeApresJour = $commandeRepo->filtrerLesCdes($user); //filter cde avec date livraison apres date du jour/du user conecté aussi
        // recuperer les cdee avant la date du jour 30jours
        $commandeAvantJour= $commandeRepo->filtrerLesAnciennesCdes($user);

        // recupere les commandes +1j apres date du jour modifiable
        $commandeEncoreModifiable=$commandeRepo->filtrerLesCdesEgalToday($user);
        // recupere les commandes +1j apres date du jour non modifiable car heure>11
        $commandeNonModifiable=$commandeRepo->filtrerLesCdesNonEgalToday($user);
        //***************************
        return $this->render('commande/voir_cde.html.twig', [ "dateToday"=>$today,"user"=>$user,
            "commandeApresJour"=>$commandeApresJour,  "commandeAvantJour"=>$commandeAvantJour, 'commandeEncoreModifiable'=>$commandeEncoreModifiable,
            'commandeNonModifiable'=>$commandeNonModifiable,
        ]);
    }

    //********************supprimer mise en avant
    /**
     * @Route("/commande-/{id}", name="supprimer-cde")
     */
    public function supprimerCde($id, EntityManagerInterface $em){
        //****************on recupere la cde
        $cdeRepo = $this->getDoctrine()->getRepository(Commande::class);
        $cde = $cdeRepo->find($id);

        //********************
        $em->remove($cde);
        $em->flush();
        return $this->redirectToRoute('voir_cde');
    }


    //********************supprimer mise en avant
    /**
     * @Route("/commande-modifier/{id}", name="modifier-cde")
     */
    public function modifierCde($id, Request $request, EntityManagerInterface $em)
    {    // on récupère l'user/ date today
        $user=$this->getUser();
        $role=$user->getRoles();
        $today = new \DateTime('now');
        // recupere toutes les familles
        $familleProduitRepo = $this->getDoctrine()->getRepository(FamilleProduit::class);
        $familleProduit = $familleProduitRepo->findAll();
        //***CDE
        $cde=new Commande();
        //****************on recupere la cde
        $cdeRepo = $this->getDoctrine()->getRepository(Commande::class);
        $commande = $cdeRepo->find($id);
        $produits=$commande->getObject();
        $this->addFlash('error', "Vous pouvez modifier cette commande.");
        $commandeForm = $this->createForm(CommandeType::class, $commande);
        $commandeForm->handleRequest($request);
        //************toutes les miseENavant apres la date du jour

        $todey=$commande->getJourDeLivraison();
        $todayTrente = new \DateTime('now');
        $todayTrente->add(new DateInterval('P30D'));
        $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
        $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
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
            if ( $commande->getNote()=="message à remplir juste avant envoi (impératif)" ) {
                //************toutes les miseENavant apres la date du jour
                $todey=$commande->getJourDeLivraison();//ligneajoutee
                $todayTrente = new \DateTime('now');
                $todayTrente->add(new DateInterval('P30D'));
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
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
            //******************************
            if ($jourSem=="dim" or $jourSem=="lun" and $role!=['ROLE_ADMIN','ROLE_USER']) {
                //************toutes les miseENavant apres la date du jour
                $todey=$commande->getJourDeLivraison();//ligneajoutee
                $todayTrente = new \DateTime('now');
                $todayTrente->add(new DateInterval('P30D'));
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
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
            $dtjour=date("d/m/Y");
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
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
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
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
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
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
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
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
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
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
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
            $produitRepo = $this->getDoctrine()->getRepository(Produit::class);
            $produits = $produitRepo->findAll();

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
            if ( $pasVide==false or $role!=['ROLE_ADMIN','ROLE_USER']) {
                //************toutes les miseENavant apres la date du jour
                $todey=$commande->getJourDeLivraison();//ligneajoutee
                $todayTrente = new \DateTime('now');
                $todayTrente->add(new DateInterval('P30D'));
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $miseEnAvant = $miseEnAvantRepo->filtrer($todayTrente, $today);//filtre ds le repository
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
            $role=$user->getRoles();
            if ( $idUtilisateur==$user->getId() or $role==['ROLE_ADMIN','ROLE_USER']){

                //**************************
                //***on enregistre la cde
                $em->persist($commande);
                $em->flush();


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
     * @Route("/commandes/{statut}", name="voir-cde-admin")
     */
    public function voirCdeAdmin($statut, Request $request, EntityManagerInterface $em)
    {    // on récupère l'user/ date today
        $user=$this->getUser();
        $today = new \DateTime('now');
        $cdeRepo = $this->getDoctrine()->getRepository(Commande::class);
        if ($statut=="commandesEnvoyee") {
            $etat=2;
            $cde = $cdeRepo->filtreCdeStatut($etat);
        }
        elseif  ($statut=="commandesReceptionnee") {
            $etat=3;
            $cde = $cdeRepo->filtreCdeStatut($etat);
        }
        elseif ($statut=="commandesTraitee") {
            $etat=4;
            $cde = $cdeRepo->filtreCdeStatut($etat);
        }
        elseif ($statut=="commandesArchivee") {
            $etat=1;
            $cde = $cdeRepo->filtreCdeStatut($etat);
        }












        //***********************************
        return $this->render('commande/voir-cde-admin.html.twig',[ "dateToday"=>$today,"user"=>$user,
            'cde'=>$cde, 'statut'=>$statut,

        ]);


    }
    //**************************************************************
}
