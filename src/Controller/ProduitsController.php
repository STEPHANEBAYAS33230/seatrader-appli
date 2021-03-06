<?php

namespace App\Controller;

use App\Entity\FamilleProduit;
use App\Entity\FiltreFamilleProduit;
use App\Entity\Produit;
use App\Form\FiltreFamilleProduitType;
use App\Form\PhotoProduitType;
use App\Form\ProduitType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route ("/admin")
 */
class ProduitsController extends AbstractController
{
    /**
     * @Route("/produits", name="ajouter-produits")
     */
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        // on récupère l'user
        $user=$this->getUser();
        // recupere toutes les familles
        try {
            $familleProduitRepo = $this->getDoctrine()->getRepository(FamilleProduit::class);
            $famille = $familleProduitRepo->findAll();

        // recupere tous les produits
        $ProduitRepo = $this->getDoctrine()->getRepository(Produit::class);
        $produit = $ProduitRepo->findAll();
        $today = strftime('%A %d %B %Y %I:%M:%S');
        } catch (\Doctrine\DBAL\Exception $e) {
            $errorMessage = $e->getMessage();
            $this->addFlash('error', 'Problème d\'accès base de données: ' . $errorMessage);
            return $this->redirectToRoute('home_connected');
        }
        //************formulaire
        $produitF = new Produit();
        $produitForm = $this->createForm(ProduitType::class, $produitF);
        $produitForm->handleRequest($request);

        //************formulaire

        $filtrefamille = new FiltreFamilleProduit();
        $filtrefamilleForm = $this->createForm(FiltreFamilleProduitType::class, $filtrefamille);
        $filtrefamilleForm->handleRequest($request);

        //******************************si filtre hydraté
        if ($filtrefamilleForm->isSubmitted() and $filtrefamilleForm->isValid()) {

            $famille = $familleProduitRepo->filtrerFamille($filtrefamille);




        }

        // si formulaire validé
        if ($produitForm->isSubmitted() and $produitForm->isValid()) {



            //sauvegarder mon produit

            try {
                $em->persist($produitF);
                $em->flush();
                //on recupere la famille
                // récupérer la famille à modifier
                $familleChoisie = $produitF->getFamille();
                $famillyRepo = $this->getDoctrine()->getRepository(FamilleProduit::class);
                $familly = $famillyRepo->find($familleChoisie);
                $familly->addProduit($produitF);
                $em->persist($familly);
                $em->flush();
                // recupere toutes les familles
                $familleProduitRepo = $this->getDoctrine()->getRepository(FamilleProduit::class);
                $famille = $familleProduitRepo->findAll();
                $filtrefamille = new FiltreFamilleProduit();
                $filtrefamille->setNom($familleChoisie);
                $famille = $familleProduitRepo->filtrerFamille($filtrefamille);
                //******************
                $this->addFlash('success', 'produit créé');
                } catch (\Doctrine\DBAL\Exception $e) {
                    $errorMessage = $e->getMessage();
                    $this->addFlash('error', 'Problème d\'enregistrement des produits: ' . $errorMessage);
                    return $this->redirectToRoute('ajouter-produits');
                }
        }


        //***************************direction
        return $this->render('produits/index.html.twig', [
            "produit"=>$produit, "familleProduit"=>$famille, "dateToday"=>$today, 'produitForm'=>$produitForm->createView(),"user"=>$user,
            "filtrefamilleForm"=>$filtrefamilleForm->createView(),
        ]);

    }
    /**
     * @Route("/produits/{id}", name="supprimer_produit")
     */
    public function supprimerProduit($id, EntityManagerInterface $em){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        //****************on recupere le produit
        try {
            $produitRepo = $this->getDoctrine()->getRepository(Produit::class);
            $prod = $produitRepo->find($id);
        }  catch (\Doctrine\DBAL\Exception $e) {
            $errorMessage = $e->getMessage();
            $this->addFlash('error', 'Problème d\'accès à la base de données: ' . $errorMessage);
            return $this->redirectToRoute('ajouter-produits');
        }
        if ($prod==null) {
            $this->addFlash('error', 'Une erreur s\'est produite pendant la suppression.');
            return $this->redirectToRoute('ajouter-produits');
        }
        try {$brochure=$prod->getBrochureFilename();
            $em->remove($prod);
            $em->flush();
            if ($brochure!=null) {
                //*****efface aussi l'image
                $nameImage = $this->getParameter("brochures_directory") . "/" . $prod->getBrochureFilename();
                //********************
                if (file_exists($nameImage)) {
                    unlink($nameImage);
                }
            }

            //********************

        } catch (\Doctrine\DBAL\Exception $e) {
            $errorMessage = $e->getMessage();
            $this->addFlash('error', 'Erreur lors de la suppression. Nous n\' avons pas pu supprimer le produit. Il se peut que ce produit soit impliquer dans les opportunités. Il faut supprimer les opportunités avant. '.$errorMessage);
            return $this->redirectToRoute('ajouter-produits');
        }
        return $this->redirectToRoute('ajouter-produits');

    }

    /**
     * @Route("/produits-photo/{id}", name="telecharger-photo_produit")
     */
    public function telechargerPhotoProduit($id, EntityManagerInterface $em,Request $request, SluggerInterface $slugger){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        //****************on recupere le produit
        try {
            $produitRepo = $this->getDoctrine()->getRepository(Produit::class);
            $prod = $produitRepo->find($id);
        } catch (\Doctrine\DBAL\Exception $e) {
            $errorMessage = $e->getMessage();
            $this->addFlash('error', 'Problème d\accès à la base de données: ' . $errorMessage);
            return $this->redirectToRoute('ajouter-produits');
        }
        //***************************
        // on récupère l'user
        $user=$this->getUser();
        $today = strftime('%A %d %B %Y %I:%M:%S');
        //***************************
        //************formulaire
        //$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $produitForm = $this->createForm(PhotoProduitType::class, $prod);
        $produitForm->handleRequest($request);
        //******************************
        if ($prod->getBrochureFilename()!=null or $prod->getBrochureFilename()!="") {
            $nomfile=$prod->getBrochureFilename();
            //$prod->setBrochureFilename("");
        } else{$nomfile="";}



        if ($produitForm->isSubmitted() && $produitForm->isValid()) {
            /** @var UploadedFile $brochureFile */
            $brochureFile = $produitForm->get('brochureFilename')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                if ($nomfile==null or $nomfile=="") {
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();
                } else {$newFilename=$nomfile;}

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('brochures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                        $errorMessage = $e->getMessage();
                        $this->addFlash('error', 'Problème détecté pendant le téléchargement: ' . $errorMessage);
                        return $this->redirectToRoute('ajouter-produits');
                    // ... handle exception if something happens during file upload
                }


                    $prod->setBrochureFilename($newFilename);
            }
            // ... persist the $product variable or any other work
            try {
                $em->persist($prod);
                $em->flush();
            } catch (FileException $e) {
                $errorMessage = $e->getMessage();
                $this->addFlash('error', 'Problème détecté pendant l\'enregistrement: ' . $errorMessage);
                return $this->redirectToRoute('ajouter-produits');
                // ... handle exception if something happens during file upload
            }

            return $this->redirectToRoute('ajouter-produits');
        }

        //**************redirection route
        return $this->render('telecharger-photo-produits/index.html.twig', [
            "prod"=>$prod, "dateToday"=>$today, 'produitForm'=>$produitForm->createView(),"user"=>$user,
        ]);

    }

    /**
     * @Route("/m-produits/{id}", name="modifier_produit")
     */
    public function modifierProduit($id, EntityManagerInterface $em,Request $request){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        // recupere toutes les familles
        try {
            $familleProduitRepo = $this->getDoctrine()->getRepository(FamilleProduit::class);
            $famille = $familleProduitRepo->findAll();
        } catch (FileException $e) {
            $errorMessage = $e->getMessage();
            $this->addFlash('error', 'Problème d\'accès à la base de données: ' . $errorMessage);
            return $this->redirectToRoute('ajouter-produits');
            // ... handle exception if something happens during file upload
        }
        // recupere tous les produits
        try {
            $ProduitRepo = $this->getDoctrine()->getRepository(Produit::class);
            $produit = $ProduitRepo->findAll();
            //****************on recupere le produit
            $produitRepo = $this->getDoctrine()->getRepository(Produit::class);
            $prodf = $produitRepo->find($id);
        } catch (FileException $e) {
            $errorMessage = $e->getMessage();
            $this->addFlash('error', 'Problème d\'accès base de données: ' . $errorMessage);
            return $this->redirectToRoute('ajouter-produits');
        }
        // on recupere la famille actuelle
        $familleActuel=$prodf->getFamille();
        // on récupère l'user
        $user=$this->getUser();
        //  on recupere la date
        $today = strftime('%A %d %B %Y %I:%M:%S');
        //************formulaire pour Produit Entity

        $produitForm = $this->createForm(ProduitType::class, $prodf);
        $produitForm->handleRequest($request);

        // si formulaire validé
        if ($produitForm->isSubmitted() and $produitForm->isValid()) {



            //sauvegarder mon produit

            try {
                $em->persist($prodf);
                $em->flush();
                // on retrouve l'ancienne famille avant modif
                $famillyRepo = $this->getDoctrine()->getRepository(FamilleProduit::class);
                $famillie = $famillyRepo->find($familleActuel);
                $famillie->removeProduit($prodf);
                $em->persist($famillie);
                $em->flush();
                //on recupere la famille
                // récupérer la famille à modifier
                $familleChoisie = $prodf->getFamille();
                $famillyRepo = $this->getDoctrine()->getRepository(FamilleProduit::class);
                $familly = $famillyRepo->find($familleChoisie);
                $familly->addProduit($prodf);
                $em->persist($familly);
                $em->flush();
            } catch (FileException $e) {
                $errorMessage = $e->getMessage();
                $this->addFlash('error', 'Problème de sauvegarde produits...etc: ' . $errorMessage);
                return $this->redirectToRoute('ajouter-produits');
            }
            //******************
            $this->addFlash('success', 'produit modifié');
            return $this->redirectToRoute('ajouter-produits', [

            ]);
        }

        //***************************direction
        return $this->render('produits/index-modif.html.twig', [
            "produit"=>$produit, "familleProduit"=>$famille, "dateToday"=>$today, 'produitForm'=>$produitForm->createView(),"user"=>$user,
        ]);


    }


    }
