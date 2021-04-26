<?php

namespace App\Controller;

use App\Entity\FamilleProduit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FamilleController extends AbstractController
{
    /**
     * @Route("/famille", name="ajouter-famille")
     */
    public function index(): Response
    {
        // on récupère l'user
        $user=$this->getUser();
        // recupere toutes les familles
        $familleProduitRepo = $this->getDoctrine()->getRepository(FamilleProduit::class);
        $famille = $familleProduitRepo->findAll();
        $today = strftime('%A %d %B %Y %I:%M:%S');

        //************formulaire
        //$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $familla = new FamilleProduit();
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












            //****************************
        return $this->render('famille/index.html.twig', [
            'controller_name' => 'FamilleController',
        ]);
    }
}
