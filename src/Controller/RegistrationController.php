<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Site;
use App\Form\RegistrationFormType;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class RegistrationController extends AbstractController
{
    #[Route('/sign-in', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, SluggerInterface $slugger, SiteRepository $siteRepository): Response
    {
        $participant = new Participant();
        $sites = $siteRepository->findAll();
        $form = $this->createForm(RegistrationFormType::class, $participant, ['sites' => $sites]);
        $form->handleRequest($request);
        $path = $this->getParameter('picture_path');


        if ($form->isSubmitted() && $form->isValid()) {

            // Récupérer l'entité Site sélectionnée dans le formulaire
            $site = $form->get('sites')->getData();
            // Associer le site au participant
            $participant->setSite($site);

            $participant->setPassword(
                $userPasswordHasher->hashPassword(
                    $participant, $form->get('password')->getData()
                )
            );

            $participant->setRoles(['ROLE_USER']);

            if ($form->get('photo')->getData() instanceof UploadedFile){
                $photo = $form->get('photo')->getData();
                $photoName = $slugger->slug($participant->getPseudo()).'-'.uniqid().'.'.$photo->guessExtension();
                $photo->move($path, $photoName);

            }


            $entityManager->persist($participant);
            $entityManager->flush();
            return $this->redirectToRoute('app_home');
        }


        return $this->render('participant/register.html.twig', [
            'registrationForm' => $form

        ]);
    }
}