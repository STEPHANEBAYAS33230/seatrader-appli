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
        // on récupère l'user
        $user = $this->getUser();
        // recupere toutes les origines
        $origineRepo = $this->getDoctrine()->getRepository(Origine::class);
        $origine = $origineRepo->findAll();
        $today = strftime('%A %d %B %Y %I:%M:%S');

        //************formulaire
        $origineAajoute = new Origine();
        $origineForm = $this->createForm(OrigineType::class, $origineAajoute);
        $origineForm->handleRequest($request);

        // si formulaire validé
        if ($origineForm->isSubmitted() and $origineForm->isValid())
        {
            //*******sauvegarde de l origine
            $em->persist($origineAajoute);
            $em->flush();

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
        //****************

        $origineRepo = $this->getDoctrine()->getRepository(Origine::class);
        $origine = $origineRepo->find($id);
        //********************
        try {
            $em->remove($origine);
            $em->flush();
        }catch (\Doctrine\DBAL\Exception $e)
        {
            $this->addFlash('error', "Impossible de supprimer l'origine (utilisation en cours dans une mise en avant)");
        }


        return $this->redirectToRoute('origine');



        //***************************



    }




}
