<?php

namespace App\Controller;

use App\Entity\MiseEnAvant;
use DateInterval;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeUserConnectedController extends AbstractController
{
    /**
     * @Route("/home/user-connected", name="home_connected_user")
     */
    public function index(): Response
    {    $today = new \DateTime('now');
        $dtplus = new \DateTime('now');
        $dtmoins= new \DateTime('now');
        $dtmoins->sub(new DateInterval('P1D'));
        $dtplus->add(new DateInterval('P1D'));
        $dtplus->format('Y-m-d');
        $dtmoins->format('Y-m-d');
        $today->format('Y-m-d');
        // on récupère l'user
        $user=$this->getUser();



        for($i=1;$i<31;$i++) {
            $today->add(new DateInterval('P1D'));
            $dtmoins->add(new DateInterval('P1D'));
            $dtplus->add(new DateInterval('P1D'));

            // récupère repository
            $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
            $miseEnAvant = $miseEnAvantRepo->filtrer($dtplus, $dtmoins);
            if (!empty($miseEnAvant)){
                break;
            }

        }
        //**************************si miseEnavant vide ds le futur (1mois)-->passé à moins 30j
        if (empty($miseEnAvant)){
            $today = new \DateTime('now');
            $dtplus = new \DateTime('now');
            $dtmoins= new \DateTime('now');
            $dtmoins->sub(new DateInterval('P1D'));
            $dtplus->add(new DateInterval('P1D'));
            $dtplus->format('Y-m-d');
            $dtmoins->format('Y-m-d');
            $today->format('Y-m-d');
            //****************mise ds le passé
            for($i=1;$i<31;$i++) {
                $today->sub(new DateInterval('P1D'));
                $dtmoins->sub(new DateInterval('P1D'));
                $dtplus->sub(new DateInterval('P1D'));

                // récupère repository
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $miseEnAvant = $miseEnAvantRepo->filtrer($dtplus, $dtmoins);
                if (!empty($miseEnAvant)){
                    break;
                }

            }
            //***********fin de IF
        }// fin recup mise en avant*********************************
        $today = new \DateTime('now');
        $today->format('Y-m-d');
        return $this->render('home_user_connected/index.html.twig', ["dateToday"=>$today, "user"=>$user,"miseEnAvant" => $miseEnAvant,

        ]);
    }
}
