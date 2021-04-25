<?php

namespace App\Controller;

use App\Entity\FamilleProduit;
use App\Entity\Produit;
use App\Form\ProduitType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProduitsController extends AbstractController
{
    /**
     * @Route("/produits", name="ajouter-produits")
     */
    public function index(EntityManagerInterface $em, Request $request): Response
    {   // recupere toutes les familles
        $familleProduitRepo = $this->getDoctrine()->getRepository(FamilleProduit::class);
        $famille = $familleProduitRepo->findAll();
        // recupere tous les produits
        $ProduitRepo = $this->getDoctrine()->getRepository(Produit::class);
        $produit = $ProduitRepo->findAll();
        $today = strftime('%A %d %B %Y %I:%M:%S');

        //************formulaire
        //$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $produitF = new Produit();
        $produitForm = $this->createForm(ProduitType::class, $produitF);
        $produitForm->handleRequest($request);

        // si formulaire validé
        if ($produitForm->isSubmitted() and $produitForm->isValid()) {



            //sauvegarder mon produit


            $em->persist($produitF);
            $em->flush();
            //on recupere la famille
            // récupérer la famille à modifier
            $familleChoisie=$produitF->getFamille();
            $famillyRepo = $this->getDoctrine()->getRepository(FamilleProduit::class);
            $familly = $famillyRepo->find($familleChoisie);
            $familly->addProduit($produitF);
            $em->persist($familly);
            $em->flush();
            //******************
            $this->addFlash('success', 'produit créé');
            return $this->redirectToRoute('ajouter-produits', [

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


        //***************************
        return $this->render('produits/index.html.twig', [
            "produit"=>$produit, "familleProduit"=>$famille, "dateToday"=>$today, 'produitForm'=>$produitForm->createView(),
        ]);

    }
    /**
     * @Route("/produits/{id}", name="supprimer_produit")
     */
    public function supprimerProduit($id, EntityManagerInterface $em,Request $request){
        //****************
        echo ($id);
        $produitRepo = $this->getDoctrine()->getRepository(Produit::class);
        $prod = $produitRepo->find($id);
        //********************
        $em->remove($prod);
        $em->flush();

        return $this->redirectToRoute('ajouter-produits');



        //***************************



    }
}
