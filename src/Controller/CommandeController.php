<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\FamilleProduit;
use App\Entity\Produit;
use App\Form\CommandeType;
use DateInterval;
use Doctrine\Common\Collections\ArrayCollection;
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
            /*var_dump($request->request->all());
            $zi=120;
            $prodt="prod".(string)$zi;
            var_dump($request->request->get($prodt,0));
            exit;*/
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
            "familleProduit"=>$familleProduit, 'produits'=>$produits,'cde'=>$cde
        ]);
    }
}
