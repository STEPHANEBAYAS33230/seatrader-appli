<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * @Route("/admin/api/post", name="api_post_index", methods={"GET"})
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
    }

    /**
     * @Route("/admin/api/post", name="api_post_store", methods={"POST"})
     */
    public function store(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
    }
}
