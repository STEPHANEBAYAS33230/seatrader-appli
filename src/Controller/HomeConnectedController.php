<?php

namespace App\Controller;

use App\Entity\Commande;
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

        $role=$user->getRoles();
        if ($role==['ROLE_USER']){
            if ($user->getEtatUtilisateur()=="INACTIF"){
                $this->addFlash('error', "Compte désactivé (veuillez contacter l'administrateur)");
                return $this->redirectToRoute('app_logout');
            }
            return $this->redirectToRoute('home_connected_user');
        }


        $today = strftime('%A %d %B %Y %I:%M:%S');
        // si l'utilisateur est admin VVVVVVV (route si dessous)
        //******recuperer les cdes envoyée
        //****************on recupere la cde
        $cdeRepo = $this->getDoctrine()->getRepository(Commande::class);
        $commandesEnvoyees = $cdeRepo->filtreCdeStatut(2);
        $commandesReceptionnee = $cdeRepo->filtreCdeStatut(3);
        $commandesTraitee = $cdeRepo->filtreCdeStatut(4);
        $commandesArchivee = $cdeRepo->filtreCdeStatut(1);

        return $this->render('home_connected/homeAdmin.html.twig', [
            'dateToday'=>$today,"user"=>$user, 'commandesEnvoyees'=>$commandesEnvoyees, 'commandesReceptionnee'=>$commandesReceptionnee,
            'commandesTraitee'=>$commandesTraitee, 'commandesArchivee'=>$commandesArchivee,
        ]);
    }
}
