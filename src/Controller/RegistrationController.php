<?php

namespace App\Controller;

use App\Entity\Participant;
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
    #[Route('/modify/{id}', name: 'app_modify', requirements: ['id' => '\d+'])]
    public function register(?Participant $participant, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, SluggerInterface $slugger, SiteRepository $siteRepository): Response
    {
        $isEditMode = $participant ? true : false;
        if (!$isEditMode) {
            $participant = new Participant();
        }

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

            if (!$isEditMode) {
                // dans le cas de la création du profil
                if ($form->get('photo')->getData() instanceof UploadedFile) {
                    $photo = $form->get('photo')->getData();
                    $photoName = $slugger->slug($participant->getPseudo()) . '-' . uniqid() . '.' . $photo->guessExtension();
                    $photo->move($path, $photoName);
                    $participant->setPhoto($photoName);

                }
                $entityManager->persist($participant);
                $entityManager->flush();
                $this->addFlash('success', 'Votre profil est créé');

                return $this->redirectToRoute('app_home');

            } else {
                //dans le cas de la modification de profil
                if ($form->get('photo')->getData() instanceof UploadedFile) {
                    $photo = $form->get('photo')->getData();
                    $photoName = $slugger->slug($participant->getPseudo()) . '-' . uniqid() . '.' . $photo->guessExtension();
                    $photo->move($path, $photoName);
                    if ($participant->getPhoto() && \file_exists($path.$participant->getPhoto())){
                        unlink($path.$participant->getPhoto());
                    }
                    $participant->setPhoto($photoName);
                }
                $entityManager->persist($participant);
                $entityManager->flush();
                $this->addFlash('success', 'Votre profil a été modifié');


                return $this->redirectToRoute('app_home',[
                    'isEditMode'=>$isEditMode,
                ]);
            }
        }


        return $this->render('participant/register.html.twig', [
            'registrationForm' => $form,
            'isEditMode'=>$isEditMode,
            'participant'=>$participant,
            'path'=>$path,

        ]);
    }
}
