<?php

namespace App\Controller;


use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\ListeSortiesType;
use App\Form\NouvelleSortieType;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/sortie', name: 'app_sortie')]
#[IsGranted('ROLE_USER')]
class SortieController extends AbstractController
{
    #[Route('/detail/{id}', name: '_details', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function details(int $id, SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);

        return $this->render('sortie/details.html.twig', [
            'sortie' => $sortie,
        ]);
    }


    #[Route('/new', name: '_new', methods: ['GET', 'POST'])]
    #[Route('/edit/{id}', name: '_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function new(?Sortie $sortie, Request $request, EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response
    {
        $isEditMode = $sortie ? true : false;
        if (!$isEditMode) {
            $sortie = new Sortie();
        }

        $form = $this->createForm(NouvelleSortieType::class, $sortie);
        //dd($form);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (!$isEditMode) {
                $sortie->setOrganisateur($this->getUser());
            }

            $ville = $form->get('ville')->getData();
            $sortie->getLieu()->setVille($ville);

            if ($sortie->isIsPublished()) {
                $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Ouverte']));
            } else {
                $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Créée']));
            }

            $site = $this->getUser()->getSite();
            $sortie->setSite($site);

            if ($isEditMode) {

                if ($this->getUser() !== $sortie->getOrganisateur()) {
                    return $this->redirectToRoute('app_sortie_new');
                }


                $user = $this->getUser();
                $organisateur = $sortie->getOrganisateur();

                $isOrganisateur = ($user->getUserIdentifier() == $organisateur->getEmail());

                $isPubliee = $sortie->isIsPublished();

                $now = new \DateTime();

                $dateLimiteInscription = $sortie->getDateLimiteInscription();
                $isEnCours = $now <= $dateLimiteInscription;

                $nbInscriptionMax = $sortie->getNbInscriptionsMax();


                $isInscriptionFull = $sortie->getParticipants()->count();

                if (($isInscriptionFull == $nbInscriptionMax) || (!$isEnCours)) {
                    $sortie = $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Cloturée']));
                } else {
                    $sortie = $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Ouverte']));
                }


                if (!$isOrganisateur || !$isPubliee || !$isEnCours) {
                    return $this->redirectToRoute('app_sortie_new');
                } else {
                    $entityManager->persist($sortie);
                    $entityManager->flush();
                    $this->redirectToRoute('app_sortie_all');
                }
            }

            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('app_sortie_all', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sortie/new.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
            'editMode' => $isEditMode,
        ]);
    }

    #[Route('/inscription/{id}', name: '_inscription', methods: ['GET'])]
    public function inscription(Sortie $sortie, Request $request, EntityManagerInterface $entityManager): Response
    {
        $participant = $this->getUser();
        $participantMax = $sortie->getNbInscriptionsMax();
        $nombreInscrit = $sortie->getParticipants()->count();

        if (($nombreInscrit < $participantMax) && ($sortie->getEtat()->getLibelle() == 'Ouverte')){

            $sortie->addParticipant($participant);

            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'inscription prise en compte');
        } else {
            $this->addFlash('warning', 'Inscription non valide');
        }
        return $this->redirectToRoute('app_sortie_all');
    }

    #[Route('/desistement/{id}', name: '_desistement', methods: ['GET'])]
    public function desistement(Sortie $sortie, ParticipantRepository $participantRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ($user && ($sortie->getEtat()->getLibelle() == 'Ouverte')) {

            $sortie->removeParticipant($user);

            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Désinscription réussie.');
        } else {
            $this->addFlash('warning', 'Désinscription non valide');
        }
        return $this->redirectToRoute('app_sortie_all');
    }


    #[Route('/delete/{id}', name: '_delete', methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $isOrganisateur = (($this->getUser())->getUserIdentifier() == ($sortie->getOrganisateur())->getEmail());

        if ($isOrganisateur || ($this->isCsrfTokenValid('delete' . $sortie->getId(), $request->request->get('_token')))) {
            $entityManager->remove($sortie);
            $entityManager->flush();
            $this->addFlash('success', 'La sortie a été supprimée');
        } else {
            $this->addFlash('error', 'Token CSRF invalide');
        }

        return $this->redirectToRoute('app_sortie_all');
    }

    #[Route('/all', name: '_all')]
    public function showAll(EtatRepository $etatRepository, SortieRepository $sortieRepository, Request $request): Response
    {
        $sorties = $sortieRepository->findAll();
        $userConnected = $this->getUser();
        $form = $this->createForm(ListeSortiesType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $datas = $form->getData();

            if ($datas['moiQuiOrganise']) {
                $datas['moiQuiOrganise'] = $userConnected;
            }
            if ($datas['moiInscrit']) {
                $datas['moiInscrit'] = $userConnected;
            }
            if ($datas['moiPasInscrit']) {
                $datas['moiPasInscrit'] = $userConnected;
            }
            if ($datas['sortiesPassees']) {
                $datas['sortiesPassees'] = $etatRepository->findOneBy(['libelle' => 'Passée']);
            }

            $sorties = $sortieRepository->findOneBySomeField($datas);
        }
        return $this->render('sortie/all-sorties.html.twig', [
            'sorties' => $sorties,
            'userConnected' => $userConnected,
            'form' => $form,
        ]);
    }

    #[Route('/sortie/publier/{id}', name: 'app_sortie_publier', requirements: ['id' => '\d+'])]
    public function publier(Sortie $sortie, EtatRepository $etatRepository, EntityManagerInterface $entityManager): Response
    {
        if ($sortie->getEtat()->getLibelle() == 'Créée') {
            $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Ouverte']));

            $entityManager->persist($sortie);
            $entityManager->flush();
        } else {
            $this->addFlash('error', 'Action interdite !');
        }

        return $this->redirectToRoute('app_sortie_all');
    }

    #[Route('/sortie/annuler/{id}', name: 'app_sortie_annuler', requirements: ['id' => '\d+'])]
    public function annuler(Sortie $sortie, EtatRepository $etatRepository, EntityManagerInterface $entityManager, SortieRepository $sortieRepository, MailerInterface $mailer): Response
    {
        if ($sortie->getEtat()->getLibelle() == 'Ouverte') {
            $participants = $sortieRepository->find($sortie->getId())->getParticipants();

            foreach ($participants as $participant) {
                $this->sendEmailModifSortie('mails/sortie-annulee.html.twig', 'Annulation de la sortie' . $sortie->getNom(), $participant, $mailer);
            }

            $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Annulée']));
            $entityManager->persist($sortie);
            $entityManager->remove($participants);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_sortie_all');
    }

    private function sendEmailModifSortie(string $emailTemplate, string $emailSubject, Participant $participant, MailerInterface $mailer): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('admin@sortir.com', 'Admin Mail Bot'))
            ->to($participant->getEmail())
            ->subject($emailSubject)
            ->htmlTemplate($emailTemplate);

        $mailer->send($email);

    }


}
