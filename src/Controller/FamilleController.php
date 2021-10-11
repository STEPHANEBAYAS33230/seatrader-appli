<?php

namespace App\Controller;

use App\Entity\FamilleProduit;
use App\Form\FamilleProduitType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FamilleController extends AbstractController
{
    /**
     * @Route("/admin/famille", name="ajouter-famille")
     */
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        // on récupère l'user
        $user = $this->getUser();
        // recupere toutes les familles
        try {
            $familleProduitRepo = $this->getDoctrine()->getRepository(FamilleProduit::class);
            $famille = $familleProduitRepo->findAll();
        } catch (\Doctrine\DBAL\Exception $e)
        {
            $this->addFlash('error', 'Erreur lors de la suppression : Nous n\' avons pas pu accéder à la base de données. '.'familleController28');
            return $this->redirectToRoute('ajouter-famille');
        }
        $today = strftime('%A %d %B %Y %I:%M:%S');

        //************formulaire
        //$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $familla = new FamilleProduit();
        $familleForm = $this->createForm(FamilleProduitType::class, $familla);
        $familleForm->handleRequest($request);

        // si formulaire validé
        if ($familleForm->isSubmitted() and $familleForm->isValid()) {


            //sauvegarder mon produit

            try {
                $em->persist($familla);
                $em->flush();
            } catch (\Doctrine\DBAL\Exception $e)
            {
                $this->addFlash('error', 'Erreur lors de la sauvegarde de la famille. '.'familleController50 '.$e);
                return $this->redirectToRoute('ajouter-famille');
            }
            //******************

            return $this->redirectToRoute('ajouter-famille', [

            ]);
        }

            //****************************
            return $this->render('famille/index.html.twig', [
                "famille" => $famille, "dateToday" => $today, 'familleForm' => $familleForm->createView(), "user" => $user,

            ]);
        }


    /**
     * @Route("/admin/famille/{id}", name="supprimer_famille")
     */
    public function supprimerProduit($id, EntityManagerInterface $em,Request $request){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        //****************
        $familleRepo = $this->getDoctrine()->getRepository(FamilleProduit::class);
        $famille = $familleRepo->find($id);
        if ($famille==null) {
            $this->addFlash('error', 'Une erreur s\'est produite pendant la suppression. '.'familleController77');
            return $this->redirectToRoute('ajouter-famille');
        }
        //********************
        $list=$famille->getListingProduits();
        if (count($list)>0){
            $this->addFlash('error', 'Erreur lors de la suppression : Nous n\' avons pas pu supprimer la famille. Il se peut que cette famille contient encore des produits. Il faut supprimer les produits avant. '.'familleController83');
            return $this->redirectToRoute('ajouter-famille');
        }
        try {
            $em->remove($famille);
            $em->flush();
            return $this->redirectToRoute('ajouter-famille');
        } catch (\Doctrine\DBAL\Exception $e)
        {
            $this->addFlash('error', 'Erreur lors de la suppression : Nous n\' avons pas pu supprimer la famille. Il se peut que cette famille contient encore des produits. Il faut supprimer les produits avant. '.'familleController92');
            return $this->redirectToRoute('ajouter-famille');
        }

    }
    }
