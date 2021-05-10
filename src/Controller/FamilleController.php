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
     * @Route("/famille", name="ajouter-famille")
     */
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        // on récupère l'user
        $user = $this->getUser();
        // recupere toutes les familles
        $familleProduitRepo = $this->getDoctrine()->getRepository(FamilleProduit::class);
        $famille = $familleProduitRepo->findAll();
        $today = strftime('%A %d %B %Y %I:%M:%S');

        //************formulaire
        //$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $familla = new FamilleProduit();
        $familleForm = $this->createForm(FamilleProduitType::class, $familla);
        $familleForm->handleRequest($request);

        // si formulaire validé
        if ($familleForm->isSubmitted() and $familleForm->isValid()) {


            //sauvegarder mon produit


            $em->persist($familla);
            $em->flush();

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
     * @Route("/famille/{id}", name="supprimer_famille")
     */
    public function supprimerProduit($id, EntityManagerInterface $em,Request $request){
        //****************

        $familleRepo = $this->getDoctrine()->getRepository(FamilleProduit::class);
        $famille = $familleRepo->find($id);
        //********************
        $em->remove($famille);
        $em->flush();

        return $this->redirectToRoute('ajouter-famille');



        //***************************



    }
    }
