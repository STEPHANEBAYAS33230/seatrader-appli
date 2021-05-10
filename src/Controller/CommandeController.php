<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\FamilleProduit;
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

        // recupere toutes les familles
        $familleProduitRepo = $this->getDoctrine()->getRepository(FamilleProduit::class);
        $familleProduit = $familleProduitRepo->findAll();
        $produitRepo = $this->getDoctrine()->getRepository(Produit::class);
        $produits = $produitRepo->findAll();
        // creer une formulaire
        //************formulaire
        $cde=new Commande();;
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
            $joursem = array('dim', 'lun', 'mar', 'mer', 'jeu', 'ven', 'sam');
// extraction des jour, mois, an de la date
            list($jour, $mois, $annee) = explode('/', $dtt);
// calcul du timestamp
            $timestamp = mktime (0, 0, 0, $mois, $jour, $annee);
// affichage du jour de la semaine
            $jourSem= $joursem[date("w",$timestamp)];
            //*********si jour livraison egale à dim ou lundi pas de cde possible
            if ($jourSem=="dim" or $jourSem=="lun") {
                $this->addFlash('error', "La livraison n'est pas ouverte pour ce jour là.");
                $commandeForm = $this->createForm(CommandeType::class, $commande);
                $commandeForm->handleRequest($request);
                foreach( $produits as $prd ) {
                    $prd->setQuantite($request->request->get('prod'.(string)$prd->getId(),0));

                }
                return $this->render('commande/index.html.twig',[ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
                    "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde,

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
            var_dump($heure);*/

            //*******************************
            if ($diff<=0) {
                $this->addFlash('error', "Erreur Date de livraison.");

                $commandeForm = $this->createForm(CommandeType::class, $commande);
                $commandeForm->handleRequest($request);
                foreach( $produits as $prd ) {
                    $prd->setQuantite($request->request->get('prod'.(string)$prd->getId(),0));

                }
                return $this->render('commande/index.html.twig',[ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
                    "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde,

                ]);
            }
            //**************erreur si livraison sup à 1mois

            if ($diff>31) {
                $this->addFlash('error', "Date de livraison >à 1mois.");
                $commandeForm = $this->createForm(CommandeType::class, $commande);
                $commandeForm->handleRequest($request);
                foreach( $produits as $prd ) {
                    $prd->setQuantite($request->request->get('prod'.(string)$prd->getId(),0));

                }
                return $this->render('commande/index.html.twig',[ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
                    "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde,

                ]);
            }
            //*********erreur si livraison j+1 et apres 11h
            if ($diff==1 and  intval($heure)>11) {
                $this->addFlash('error', "heure dépassée pour livraison le lendemain (avant 11h)");
                $commandeForm = $this->createForm(CommandeType::class, $commande);
                $commandeForm->handleRequest($request);
                foreach( $produits as $prd ) {
                    $prd->setQuantite($request->request->get('prod'.(string)$prd->getId(),0));

                }
                return $this->render('commande/index.html.twig',[ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
                    "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde,

                ]);
            }
            //********erreur de date inf à la date du jour
            if (intval($dttjour)>=intval($jour) and intval($dttmois)==intval($mois) and intval($dttan)==intval($annee) ) {
                $this->addFlash('error', "Problème de date de livraison.");
                $commandeForm = $this->createForm(CommandeType::class, $commande);
                $commandeForm->handleRequest($request);
                foreach( $produits as $prd ) {
                    $prd->setQuantite($request->request->get('prod'.(string)$prd->getId(),0));

                }
                return $this->render('commande/index.html.twig',[ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
                    "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde,

                ]);
            }
            if (intval($dttmois)>intval($mois) and intval($dttan)==intval($annee) ) {
                $this->addFlash('error', "Problème de date de livraison.");
                $commandeForm = $this->createForm(CommandeType::class, $commande);
                $commandeForm->handleRequest($request);
                foreach( $produits as $prd ) {
                    $prd->setQuantite($request->request->get('prod'.(string)$prd->getId(),0));

                }
                return $this->render('commande/index.html.twig',[ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
                    "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde,

                ]);
            }
            if (intval($dttan)>intval($annee) ) {
                $this->addFlash('error', "Problème de date de livraison.");
                $commandeForm = $this->createForm(CommandeType::class, $commande);
                $commandeForm->handleRequest($request);
                foreach( $produits as $prd ) {
                    $prd->setQuantite($request->request->get('prod'.(string)$prd->getId(),0));

                }
                return $this->render('commande/index.html.twig',[ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
                    "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde,

                ]);
            }


            //******************************
            //** MANQUE VOIR SI DATE OUVERTE(pour dim/et lun) ou bloqué(pour mar/mer/jeu/ven/sam) PAR ADMIN
            //******************************
            // recupere toutes les produits
            $produitRepo = $this->getDoctrine()->getRepository(Produit::class);
            $produits = $produitRepo->findAll();
            foreach( $produits as $prd ) {
                $prd->setQuantite($request->request->get('prod'.(string)$prd->getId(),0));
            //$commande->add($prd);
            //$prd->addcommande($commande);
                $commande->setEtatCommande("envoyée");
            $commande->setObject($produits);
                $em->persist($prd);
            }
            //***on enregistre la cde
            $em->persist($commande);
            $em->flush();
            $cdeRepo = $this->getDoctrine()->getRepository(Commande::class);
            $cde = $cdeRepo->find(35);

            //***********redirection vers voir mes commandes
        }


            return $this->render('commande/index.html.twig', [ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
            "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde,
        ]);
    }
}
