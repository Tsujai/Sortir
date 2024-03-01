<?php

namespace App\Controller;

use App\Repository\ParticipantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ParticipantController extends AbstractController
{
    #[Route('/profil/{id}', name: 'app_profil_detail', requirements: ['id'=>'\d+'])]
    public function detail(int $id, ParticipantRepository $participantRepository): Response
    {
        $participant = $participantRepository->find($id);


        return $this->render('participant/profil.html.twig', [
            'participant' => $participant,
            'photo_dir' =>$this->getParameter( 'picture_path')
        ]);
    }

    #[Route('/liste-Utilisateurs', name: 'app_liste_utilisateurs')]
    public function listeParticipants(ParticipantRepository $participantRepository): Response
    {
        $userConnected = $this->getUser();
        $participants = $participantRepository->findAll();

        return $this->render('participant/listeUtilisateurs.html.twig', [
            'participants' => $participants,
            'userConnected'=> $userConnected,
        ]);
    }
 }
