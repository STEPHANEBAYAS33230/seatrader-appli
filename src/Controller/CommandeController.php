<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\FamilleProduit;
use App\Entity\Produit;
use App\Form\CommandeType;
use DateInterval;
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
            //******************** jour en francais
            $dtt=date_format($dateVoulue,"d/m/Y");
            //$dtt=$dateVoulue;
            // tableau des jours de la semaine date_format($date,"Y/m/d H:i:s");
            //$dtt=date("d/m/Y");
            $joursem = array('dim', 'lun', 'mar', 'mer', 'jeu', 'ven', 'sam');
// extraction des jour, mois, an de la date
            list($jour, $mois, $annee) = explode('/', $dtt);
// calcul du timestamp
            $timestamp = mktime (0, 0, 0, $mois, $jour, $annee);
// affichage du jour de la semaine
            $jour= $joursem[date("w",$timestamp)];
            //*********si jour livraison egale à dim ou lundi pas de cde possible
            if ($jour=="dim" or $jour=="lun") {
                $this->addFlash('error', "La livraison n'est pas ouverte pour ce jour là.");
                return $this->redirectToRoute('faire_cde');
            }
            //**********controle si date du jour <24h date de liv
            $dttjour=date("d");
            $dttmois=date("m");
            $dttan=date("Y");

            //********erreur de date inf à la date du jour
            if (intval($dttjour)>=intval($jour) and intval($dttmois)==intval($mois) and intval($dttan)==intval($annee) ) {
                $this->addFlash('error', "Problème de date de livraison.");
                return $this->redirectToRoute('faire_cde');
            }
            if (intval($dttmois)>intval($mois) and intval($dttan)==intval($annee) ) {
                $this->addFlash('error', "Problème de date de livraison.");
                return $this->redirectToRoute('faire_cde');
            }
            if (intval($dttan)>intval($annee) ) {
                $this->addFlash('error', "Problème de date de livraison.");
                return $this->redirectToRoute('faire_cde');
            }

            //**************erreur si livraison sup à 1mois
            $dtjour=date("d/m/Y");
            $diff = round((strtotime($dtt) - strtotime($dtjour))/(60*60*24)-1);
            if ($diff>31) {
                $this->addFlash('error', "Date de livraison >à 1mois.");
                return $this->redirectToRoute('faire_cde');
            }
            //*********erreur si livraison j+1 et apres 11h
            if ($diff==1 and  (intval(date('H')))>11) {
                $this->addFlash('error', "heure dépassée pour livraison le lendemain (avant 11h)");
                return $this->redirectToRoute('faire_cde');
            }
            //******************************
            //** MANQUE VOIR SI DATE OUVERTE PAR ADMIN
            //******************************
            // recupere toutes les produits
            $produitRepo = $this->getDoctrine()->getRepository(Produit::class);
            $produits = $produitRepo->findAll();
            foreach( $produits as $prd ) {
                $prd->setQuantite($request->request->get('prod'.(string)$prd->getId(),0));
            //$commande->add($prd);
            //$prd->addcommande($commande);
            $commande->setObject($produits);
                $em->persist($prd);
            }
            //***on enregistre la cde
            $em->persist($commande);
            $em->flush();
            $cdeRepo = $this->getDoctrine()->getRepository(Commande::class);
            $cde = $cdeRepo->find(35);

            /*var_dump($cde->getListeProduits(),null);
            $produit=$cde->getListeProduits();
            foreach( $produit as $prd ) {
            var_dump($prd->getId());
            var_dump($prd->getQuantite());}

            exit;*/
        }


            return $this->render('commande/index.html.twig', [ "dateToday"=>$today,"user"=>$user, "commandeForm"=>$commandeForm->createView(),
            "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde,
        ]);
    }
}
