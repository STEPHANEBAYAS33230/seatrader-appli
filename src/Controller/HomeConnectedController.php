<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeConnectedController extends AbstractController
{
    /**
     * @Route("/home/connected", name="home_connected")
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        // on récupère l'user
        $user=$this->getUser();
        $role=$user->getRoles();
        if ($user->getEtatUtilisateur()=="INACTIF"){
            $this->addFlash('error', "Compte désactivé (veuillez contacter l'administrateur)");
            return $this->redirectToRoute('app_logout');
        }
        $role=$user->getRoles();
        if ($role==['ROLE_USER']){
            return $this->redirectToRoute('home_connected_user');
        }


        $today = strftime('%A %d %B %Y %I:%M:%S');
        return $this->render('home_connected/homeAdmin.html.twig', [
            'dateToday'=>$today,"user"=>$user,
        ]);
    }
}
