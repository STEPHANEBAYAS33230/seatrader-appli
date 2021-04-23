<?php

namespace App\Controller;

use App\Entity\FamilleProduit;
use App\Entity\Produit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProduitsController extends AbstractController
{
    /**
     * @Route("/produits", name="ajouter-produits")
     */
    public function index(): Response
    {   // recupere toutes les familles
        $familleProduitRepo = $this->getDoctrine()->getRepository(FamilleProduit::class);
        $famille = $familleProduitRepo->findAll();
        // recupere tous les produits
        $ProduitRepo = $this->getDoctrine()->getRepository(Produit::class);
        $produit = $ProduitRepo->findAll();
        $today = strftime('%A %d %B %Y %I:%M:%S');
        return $this->render('produits/index.html.twig', [
            "produit"=>$produit, "familleProduit"=>$famille, "dateToday"=>$today,
        ]);

    }
}
