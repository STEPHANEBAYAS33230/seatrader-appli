<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Form\CoursType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class CoursController extends AbstractController
{

    /**
     * @Route("/cours", name="mettre_le_cours_ligne")
     */
    public function telechargerPDFcours( EntityManagerInterface $em,Request $request, SluggerInterface $slugger){
        $cours=null;
        //***************************
        // on récupère l'user
        $user=$this->getUser();
        $id=$user->getId();
        $today = strftime('%A %d %B %Y %I:%M:%S');
        //***************************
        //****************on recupere le produit
        $coursRepo = $this->getDoctrine()->getRepository(Cours::class);
        $lecours = $coursRepo->trouver($user);

        if ($lecours==null){
            $cours= new Cours();
            $cours->setUtilisateur($user);
        } else {$cours=$lecours[0];}

        //$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $coursForm = $this->createForm(CoursType::class, $cours);
        $coursForm->handleRequest($request);
        //******************************
        if ($cours->getBrochureFilename()!=null or $cours->getBrochureFilename()!="") {
            $nomfile=$cours->getBrochureFilename();
            //$prod->setBrochureFilename("");
        } else{$nomfile="";}



        if ($coursForm->isSubmitted() && $coursForm->isValid()) {
            /** @var UploadedFile $brochureFile */
            $brochureFile = $coursForm->get('brochureFilename')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                if ($nomfile==null or $nomfile=="") {
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();
                } else {$newFilename=$nomfile;}

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('brochures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the image file name
                // instead of its contents
                //$prod->setBrochureFilename($newFilename);

                $cours->setBrochureFilename($newFilename);
            }
            // ... persist the $product variable or any other work
            $em->persist($cours);
            $em->flush();
            return $this->redirectToRoute('home_connected');
        }

        //**************redirection route
        return $this->render('cours/index.html.twig', [
            "dateToday"=>$today, 'coursForm'=>$coursForm->createView(),"user"=>$user,
        ]);

    }
}
