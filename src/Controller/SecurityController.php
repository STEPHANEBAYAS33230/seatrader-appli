<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils,  MailerInterface $mailer, Environment $twig): Response
    {


        $nombre=0.01;
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }
        /**if ($utilisateur!=null and $nomDNS==$identifiant." " and $roles==['ROLE_ADMIN','ROLE_USER']) {
        $nombre = rand(10000, 99999);
        //envoi mail
        $email = (new TemplatedEmail())
        ->from('contact@seatrader.eu')
        ->to('contact@seatrader.eu')
        //->cc('cc@example.com')
        //->bcc('bcc@example.com')
        //->replyTo('fabien@example.com')
        ->priority(Email::PRIORITY_HIGH)
        ->subject('code accès seatrader-appli')
        ->text('Voici le code d\'accès pour identifier sur site seatrader-appli: ' . $nombre)
        ->htmlTemplate('mail/mail.html.twig');
        $mailer->send($email);
        }**/
        // get the login error if there is one
       $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
       $lastUsername = $authenticationUtils->getLastUsername();
       // $lastUsername=null;
        //$error=null;
        $nomDNS="";
        $role=['0','0'];
        $phase=0;
        return $this->render('security/login.html.twig', ['last_username' => $lastUsername,
            'error' => $error,'role'=>$role,'nomDNS'=>$nomDNS, 'nombre'=>$nombre, 'phase'=>$phase]);
        //return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error,'nomDNS'=>$nomDNS]);

    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/login-find/recherche/{identifiant}", name="login-trouve")
     */
    public function loginTrouve($identifiant, AuthenticationUtils $authenticationUtils, MailerInterface $mailer, Environment $twig): Response
    {   $nombre=0.01;
        try{
            $utilisateurRepo = $this->getDoctrine()->getRepository(Utilisateur::class);
            $utilisateur = $utilisateurRepo->trouverUtilisateur($identifiant." ");//." "
        } catch (\Doctrine\DBAL\Exception $e)
        {
            $this->addFlash('error', 'Problème d\'accès à la base de données: ');
            return $this->redirectToRoute('/');
        }
        if ($utilisateur!=null and $identifiant." "==$utilisateur[0]->getNomDeLaSociete())
        {
            $role=$utilisateur[0]->getRoles();
            $nomDNS=$utilisateur[0]->getNomDeLaSociete();
            $phase=2;
        }
        else{
            $nomDNS=$identifiant;
            $role=['0','0'];
            $phase=3;
        }

        if ($utilisateur!=null  and $role==['ROLE_ADMIN','ROLE_USER']) {//." " identifiant
        $nombre = rand(10000, 99999);
        //envoi mail
        $email = (new TemplatedEmail())
        ->from('contact@seatrader.eu')
        ->to('contact@seatrader.eu')
        //->cc('cc@example.com')
        //->bcc('bcc@example.com')
        //->replyTo('fabien@example.com')
        ->priority(Email::PRIORITY_HIGH)
        ->subject('code accès seatrader-appli')
        ->text('Voici le code d\'accès pour identifier sur site seatrader-appli: ' . $nombre)
        ->htmlTemplate('mail/mail.html.twig');
        $mailer->send($email);
        }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername,
            'error' => $error,'role'=>$role,'nomDNS'=>$nomDNS, 'nombre'=>$nombre, 'phase'=>$phase]);
    }
}
