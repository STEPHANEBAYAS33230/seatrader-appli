<?php

namespace App\Controller;


use App\Entity\Origine;
use App\Form\OrigineType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/*********************************************************************************************/
/*                       CREER DES ORIGINES                                                  */
/*********************************************************************************************/

/**
 * @Route ("/admin")
 */
class OrigineController extends AbstractController
{
    /**
     * @Route("/origine", name="origine")
     */
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN'); // acess denied si personne n est pas authentifier ADMIN
        // on récupère l'user
        $user = $this->getUser();
        // recupere toutes les origines
        try {
            $origineRepo = $this->getDoctrine()->getRepository(Origine::class);
            $origine = $origineRepo->findAll();
        } catch (\Doctrine\DBAL\Exception $e) {
            $errorMessage = $e->getMessage();
            $this->addFlash('error', 'Problème d\'accès base de données: ' . $errorMessage);
            return $this->redirectToRoute('home_connected');
        }
        $today = strftime('%A %d %B %Y %I:%M:%S');

        //************formulaire
        $origineAajoute = new Origine();
        $origineForm = $this->createForm(OrigineType::class, $origineAajoute);
        $origineForm->handleRequest($request);

        // si formulaire validé
        if ($origineForm->isSubmitted() and $origineForm->isValid())
        {
            //*******sauvegarde de l origine
            try {
            $em->persist($origineAajoute);
            $em->flush();
            } catch (\Doctrine\DBAL\Exception $e) {
                $errorMessage = $e->getMessage();
                $this->addFlash('error', 'Nous n\'avons pas pu ajouter cette origine: ' . $errorMessage);
                return $this->redirectToRoute('origine');
            }
            //******************redirection
            return $this->redirectToRoute('origine', [
            ]);
        }
        return $this->render('origine/index.html.twig', [
            "origine" => $origine, "dateToday" => $today, 'origineForm' => $origineForm->createView(), "user" => $user,
        ]);
    }
    //************function supprimer-origine
    /**
     * @Route("/origine/{id}", name="supprimer-origine")
     */
    public function supprimerOrigin($id, EntityManagerInterface $em,Request $request){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        try {
        $origineRepo = $this->getDoctrine()->getRepository(Origine::class);
        $origine = $origineRepo->find($id);
        } catch (\Doctrine\DBAL\Exception $e) {
            $errorMessage = $e->getMessage();
            $this->addFlash('error', 'Problème d\'accès base de données: ' . $errorMessage);
            return $this->redirectToRoute('origine');
        }
        //********************
        try {
            $em->remove($origine);
            $em->flush();
        }catch (\Doctrine\DBAL\Exception $e)
        {
            $errorMessage = $e->getMessage();
            $this->addFlash('error', "Impossible de supprimer l'origine (utilisation en cours dans une mise en avant?): ".$errorMessage);
        }


        return $this->redirectToRoute('origine');



        //***************************



    }
}
