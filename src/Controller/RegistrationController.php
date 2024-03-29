<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\RegistrationFormType;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

class RegistrationController extends AbstractController
{
    #[Route('/sign-in', name: 'app_register')]
    #[Route('/modify', name: 'app_modify')]
    public function register(ParticipantRepository $participantRepository, MailerInterface $mailer, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, SluggerInterface $slugger, SiteRepository $siteRepository): Response
    {
        $id = $request->get('id');

        $isEditMode = $id ? true : false;

        if ($isEditMode){
            $participant = $participantRepository->find($id);
        }else{
            $participant = new Participant();
        }


        if ($isEditMode && !$this->isGranted('IS_AUTHENTICATED')){
            $this->addFlash('error', 'Arrache toi de là t\'es pas d\'ma bande, casse toi tu...');
            throw $this->createAccessDeniedException();
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

                $this->sendEmailInscription('mails/inscription.html.twig', 'Inscription à Sortir.com ',$participant, $mailer);


                return $this->redirectToRoute('app_home');

            } else if ($isEditMode && $this->isGranted('ROLE_USER')) {
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
            } else {
                $this->addFlash('warning', 'Arrache toi de là t\'es pas d\'ma bande, casse toi tu...');
                return $this->redirectToRoute('app_home');
            }
        }


        return $this->render('participant/register.html.twig', [
            'registrationForm' => $form,
            'isEditMode'=>$isEditMode,
            'participant'=>$participant,
            'path'=>$path,

        ]);
    }

    private function sendEmailInscription(string $emailTemplate, string $emailSubject, Participant $participant, MailerInterface $mailer) : void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@sortir.com', 'infos-sortir.com'))
            ->to($participant->getEmail())
            ->subject($emailSubject)
            ->htmlTemplate($emailTemplate);
        ;

        $mailer->send($email);
    }
}
