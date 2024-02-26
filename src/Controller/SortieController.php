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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

                $isEnCours = $now < $etatRepository->findOneBy(['libelle' => 'Activité en cours']);

                $nbInscriptionMax = $sortie->getNbInscriptionsMax();
                $dateLimiteInscription = $sortie->getDateLimiteInscription();

                if(($isInscriptionFull = $nbInscriptionMax) || ($isInscriptionFull = $$dateLimiteInscription) ){
                    $sortie = $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Cloturée']));
                }else{
                    $sortie = $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Ouverte']));
                }



//                $isInscriptionFull =

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

    #[Route('/inscription/{id}', name: '_inscription',methods: ['GET'])]
    public function inscription(Sortie $sortie, Participant $participant, Request $request,EntityManagerInterface $entityManager):void{
        $user = $this->getUser();
        $participantMax = $sortie->getNbInscriptionsMax();
        $nombreInscrit = $sortie->getParticipants()->count();

        if($nombreInscrit < $participantMax){
            $participant = new Participant();

            $user->$sortie->addParticipant($participant);
            $participant->addSortie($sortie);

            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', 'inscription prise en compte');
        }else{
            $this->addFlash('warning', 'Sortie complète');
        }
    }

    #[Route('/desistement/{id}', name: '_desistement',methods: ['GET'])]
    public function desistement(Sortie $sortie, Participant $participant, ParticipantRepository $participantRepository, Request $request,EntityManagerInterface $entityManager):void
    {
        $user = $this->getUser();
        $nombreInscrit = $sortie->getParticipants()->count();
        $participant = $participantRepository->findOneBy(['sortie' => $sortie, 'user' => $user]);

        if($participant){

            $user->$sortie->removeParticipant($participant);

            $entityManager->remove($participant);
            $entityManager->flush();

            $this->addFlash('success', 'Désinscription réussie.');
        } else {
            $this->addFlash('error', 'Vous n\'êtes pas inscrit à cette sortie.');
        }
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

        return $this->redirectToRoute('app_sortie_index');
    }


    #[Route('/all', name: '_all')]
    public function showAll(SortieRepository $sortieRepository, Request $request): Response
    {
        $sorties = $sortieRepository->findAll();
        $userConnected = $this->getUser();
        $form = $this->createForm(ListeSortiesType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('site')->getData() != null) {
                $site = $form->get('site')->getData();
            }
            if ($form->get('search')->getData() != null) {
                $search = $form->get('search')->getData();
            }
            if ($form->get('firstDate')->getData() != null) {
                $firstDate = $form->get('firstDate')->getData();
            }
            if ($form->get('secondDate')->getData() != null) {
                $secondDate = $form->get('secondDate')->getData();
            }
            if ($form->get('moiQuiOrganise')->getData()) {
                $moiQuiOrganise = $userConnected;
            }
            if ($form->get('moiInscrit')->getData()) {
                $moiInscrit = $userConnected;
            }
            if ($form->get('moiPasInscrit')->getData()) {
                $moiPasInscrit = $userConnected;
            }
            if ($form->get('sortiesPassees')->getData()) {
                $sortiesPassees = 'Passée';
            }

            $sorties = $sortieRepository->findOneBySomeField($site, $search, $firstDate, $secondDate, $moiQuiOrganise, $moiInscrit, $moiPasInscrit, $sortiesPassees);
        }
        return $this->render('sortie/all-sorties.html.twig', [
            'sorties' => $sorties,
            'userConnected' => $userConnected,
            'form' => $form,
        ]);

    }
}
