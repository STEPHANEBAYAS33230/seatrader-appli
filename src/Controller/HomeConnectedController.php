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
        $today = strftime('%A %d %B %Y %I:%M:%S');
        return $this->render('home_connected/homeAdmin.html.twig', [
            'dateToday'=>$today,
        ]);
    }
}
