<?php

namespace App\Controller;

use App\Entity\Formulaire;
use App\Entity\MiseEnAvant;
use App\Form\FormulaireType;
use DateInterval;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;


class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request, MailerInterface $mailer, Environment $twig, $publicDir): Response
    {
        //*********creation du formulaire
        $formulaire=new Formulaire();
        $formulaireForm = $this->createForm(FormulaireType::class, $formulaire);
        $formulaireForm->handleRequest($request);
        $user = $this->getUser();
        if ($user != null){
            $role = $user->getRoles();
        if ($role == ['ROLE_USER'] ) {
            return $this->redirectToRoute('home_connected');
        }
        }
        //*********recuperer les mises avant/date
        $today = new \DateTime('now');
        $dtplus = new \DateTime('now');
        $dtmoins= new \DateTime('now');
        $dtmoins->sub(new DateInterval('P1D'));
        $dtplus->add(new DateInterval('P1D'));
        $dtplus->format('Y-m-d');
        $dtmoins->format('Y-m-d');
        $today->format('Y-m-d');


        for($i=1;$i<31;$i++) {
            $today->add(new DateInterval('P1D'));
            $dtmoins->add(new DateInterval('P1D'));
            $dtplus->add(new DateInterval('P1D'));

            // récupère repository
            try {
                $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                $miseEnAvant = $miseEnAvantRepo->filtrer($dtplus, $dtmoins);
            } catch (\Doctrine\DBAL\Exception $e) {
                $errorMessage = $e->getMessage();
                $this->addFlash('error', 'Problème d\'accès à la base de données: ' . 'homeController59');
                 return $this->redirectToRoute('home');
            }
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
                try {
                    $miseEnAvantRepo = $this->getDoctrine()->getRepository(MiseEnAvant::class);
                    $miseEnAvant = $miseEnAvantRepo->filtrer($dtplus, $dtmoins);
                } catch (\Doctrine\DBAL\Exception $e) {
                    $errorMessage = $e->getMessage();
                    $this->addFlash('error', 'Problème d\'accès à la base de données: ' . 'homeController89');
                    return $this->redirectToRoute('home');
                }
                if (!empty($miseEnAvant)){
                    break;
                }

            }
        //***********fin de IF
        }
        if ($formulaireForm->isSubmitted() and $formulaireForm->isValid())
        {
            $from=$formulaire->getEmail();
            $message=$formulaire->getMessage();
            $number=$formulaire->getTelephone();
            $nom=$formulaire->getNom();

            //envoi mail
            $email = (new TemplatedEmail())
                ->from($from)
                ->to('contact@seatrader.eu')
                //->cc('cc@example.com')
                //->bcc('bcc@example.com')
                //->replyTo('fabien@example.com')
                ->priority(Email::PRIORITY_HIGH)
                ->subject('formulaire de contact seatrader-appli')
                ->text('email: '.$from.' nom: '.$nom.' vous a envoyé ce message du site seatrader-appli: '.$message.' tel:'.$number)
                ->htmlTemplate( 'mail/mail2.html.twig')
                ->attachFromPath( $publicDir.'/public/assets/images/slide-01.jpg','/public/assets/images/seatraderLOGOsmall.png')
                ->attachFromPath( $publicDir.'/public/assets/images/seatraderBIG.png')
                ->context([
                    'message' => $message,
                    'from' => $from,
                    'nom' =>  $nom,
                     'number' => $number
                ]);
            $mailer->send($email);
            $message='Votre message a bien été envoyé à la société Seatrader. Vous serez contacté dans les plus brefs délais. Cordialement Stéphane Moncorgé';
            //envoi mail2
            $email = (new TemplatedEmail())
                ->from('contact@seatrader.eu')
                ->to($from)
                //->cc('cc@example.com')
                //->bcc('bcc@example.com')
                //->replyTo('fabien@example.com')
                ->priority(Email::PRIORITY_HIGH)
                ->subject('Accusé de réception: message envoyé')
                ->text('Votre message a bien été envoyé à la société Seatrader. Vous serez contacter dans les plus brefs délais. Stéphane Moncorgé')
                ->htmlTemplate( 'mail/mail.html.twig')
                ->attachFromPath( $publicDir.'/public/assets/images/slide-01.jpg')
                ->attachFromPath( $publicDir.'/public/assets/images/seatraderBIG.png')
                ->context([
                    'message' => $message,
                    'from' => $from,
                    '$nom' =>  $nom
                ]);
                $mailer->send($email);
            return $this->redirectToRoute('home', [

            ]);


        }





        //****************************************************************
        return $this->render('home/index.html.twig', [
            "miseEnAvant" => $miseEnAvant, "today"=>$today, 'formulaireForm'=>$formulaireForm->createView(),

        ]);
    }
}
