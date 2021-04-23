<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CreerUnUtilisateurController extends AbstractController
{
    /**
     * @Route("/creer/un/utilisateur", name="creerUnUtilisateur")
     */
    public function index(EntityManagerInterface $em, Request $request, UserPasswordEncoderInterface $encoder): Response
    {   $today = strftime('%A %d %B %Y %I:%M:%S');
        //$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $participant = new Utilisateur();
        $registerForm = $this->createForm(UtilisateurType::class, $participant);
        $registerForm->handleRequest($request);

        // si formulaire validé
        if ($registerForm->isSubmitted() and $registerForm->isValid()) {

            //hasher le mot de passe avec class passwordEncoderInterface
            $hashed=$encoder->encodePassword($participant,$participant->getPassword());
            $participant->setPassword($hashed);

            //sauvegarder mon utilsateur
            //try{

            $em->persist($participant);
            $em->flush();
            $this->addFlash('success', 'le compte a été créé avec succès (veuillez-vous connecter maintenant)');
            return $this->redirectToRoute('home_connected', [

            ]);

            //} catch (\Doctrine\DBAL\Exception $e) {
            //    $errorMessage = $e->getMessage();
            //   echo ($errorMessage);
            //  $this->addFlash('error', 'Nous n\' avons pas pu créer le compte (email existant...etc)');
            // return $this->redirectToRoute('home', [
            //   'controller_name' => 'HomeController',
            //  ]);
            // }

        }
        return $this->render('creer_un_utilisateur/index.html.twig', [
            'controller_name' => 'UserController', "registerForm"=>$registerForm->createView(), 'dateToday'=>$today,
        ]);

    }
}
