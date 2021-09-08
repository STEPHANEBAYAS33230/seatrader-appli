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

// @Route ("/admin") signifie que uniquement l'administrateur connecté aura acces au controller et ses fonctionnalités
// configuré dans le security.yaml--- voir annotation ci-dessous
/**
 *
 * @Route ("/admin")
 */
class CoursController extends AbstractController
{
//parametre d'annotatios de route: l'URL
    /**
     * @Route("/cours", name="mettre_le_cours_ligne")
     */
    public function telechargerPDFcours(EntityManagerInterface $em, Request $request, SluggerInterface $slugger) : Response
    {   // EntityManagerInterface $em gestionnaire d'entité
        // objet de Requete qui contient toutes les info qui ont été generé par la requete au serveur ex formulaire
        // Response: chacune de nos methodes presente ds le controller doit toujours retourner 'faire un return d'une objet response
        //          de htttp foundation ex $this->render
        // SluggerInterface $slugger: pour la gestion de  la string du nom du pdf sauvegarder dans brochures_directory (public/uploads/brochures)
        //                              nom+"-"+key+.pdf ex: TSUD-60a7c8076ebc2.pdf
        //controller uniq acces à l'administrateur
        $this->denyAccessUnlessGranted('ROLE_ADMIN'); // acess denied si personne n est pas authentifier ADMIN
        $cours = null;
        $user = $this->getUser();  //on récupère l'user(admin obligaoire)
        $id = $user->getId();
        //recup date du jour
        $today = strftime('%A %d %B %Y %I:%M:%S');
        //****************on recupere le nom de la filename du cours du user
        try {
            $coursRepo = $this->getDoctrine()->getRepository(Cours::class);
            $lecours = $coursRepo->trouver($user); //avec la methode trouver(...) du repository CoursRepository.php
            // si il n'y a pas eu encore de cours enregisté (c a dire que c'est la premiere fois)
        } catch (\Doctrine\DBAL\Exception $e)
        {
            $errorMessage = $e->getMessage();
            $this->addFlash('error', 'Problème de connexion à la base de données/'.'coursController50');
            return $this->redirectToRoute('home_connected', [ ]);
        }

        if ($lecours == null) { //si null on cree un nouveau cours
            $cours = new Cours();
            $cours->setUtilisateur($user);
        } else {
            $cours = $lecours[0]; //on recuprere le nom de l'ancien filename du cours sauvegardé
        }
        $coursForm = $this->createForm(CoursType::class, $cours);//*** on prepare le formulaire
        $coursForm->handleRequest($request);
        //*********si il y a deja un cours, on lui redonnera le meme nom
        //******** pour ne pas en creer un nouveau(on remplace celui existant)
        // si non $nomfile="" et du coup il prendra le nom du fichier telecharger
        if ($cours->getBrochureFilename() != null or $cours->getBrochureFilename() != "") {
            $nomfile = $cours->getBrochureFilename();
        } else {
            $nomfile = "";
        }
        if ($coursForm->isSubmitted() && $coursForm->isValid()) {//si le formulaire est soumis
            /** @var UploadedFile $brochureFile */
            $brochureFile = $coursForm->get('brochureFilename')->getData();
            //Condition car le champ n'est pas required
            // le fichier PDF doit donc être traité uniquement lorsqu'un fichier est téléchargé
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // nécessaire pour inclure en toute sécurité le nom du fichier dans le cadre de l'URL
                $safeFilename = $slugger->slug($originalFilename);
                if ($nomfile == null or $nomfile == "") {
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();
                } else {
                    $newFilename = $nomfile;
                }
                // on déplace le fichier dans le répertoire où sont stockées les brochures
                try {
                    $brochureFile->move(
                        $this->getParameter('brochures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // si une exception se produit pendant le telechargement/ ajout d'une message flash avant redirection
                    $this->addFlash('error', "Une erreur s'est produite pendant le téléchargement: ".'courscontroller92');
                    return $this->redirectToRoute('mettre_le_cours_ligne'); //redirection vers un controller
                }
                // *** ecriture du nom du cours de produits
                $cours->setBrochureFilename($newFilename);
            }
            // ... on sauvegarde ds la bdd et on redirige vers pas accueil connecté
            try {
                $em->persist($cours);
                $em->flush();
                $this->addFlash('success', 'le cours des produits est en ligne.');
                return $this->redirectToRoute('home_connected'); //redirection vers un controller
                // redirectToRoute est forme reduite de return new RedirectResponse($this->generateUrl('home_connected'));
            } catch (\Doctrine\DBAL\Exception $e)
            {
                $errorMessage = $e->getMessage();
                $this->addFlash('error', 'Nous n\' avons pas pu mettre en ligne le cours des produits/ '.'courscontroller108');
                return $this->redirectToRoute('home_connected', [ ]);
            }

        }
        //***direction vers le template twig/ je fourni à mon template la date du jour/le formulaire/mon utilisateur
        return $this->render('cours/index.html.twig', [
            "dateToday" => $today, 'coursForm' => $coursForm->createView(), "user" => $user,
        ]);
    }
}