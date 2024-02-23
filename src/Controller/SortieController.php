<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Wish;
use App\Form\NouvelleSortieType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

#[Route('/sortie', name: 'app_sortie')]
class SortieController extends AbstractController
{

    #[Route('/{id}', name: '_details', requirements: ['id' => '\d+'] , methods: ['GET'])]
    public function details(int $id, SortieRepository $sortieRepository): Response
    {
            $sortie = $sortieRepository->find($id);

        return $this->render('sortie/details.html.twig', [
            'sortie' => $sortie,
        ]);
    }


    #[Route('/new', name: '_new', methods: ['GET', 'POST'])]
    #[Route('/{id}/edit', name: '_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function new(?Sortie $sortie, Request $request, EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response
    {
        $isEditMode = $sortie ? true : false;
        if(!$isEditMode) {
            $sortie = new Sortie();
        }

        $form = $this->createForm(NouvelleSortieType::class, $sortie);
        //dd($form);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $sortie->setOrganisateur($this->getUser());

            $ville = $form->get('ville')->getData();
            $sortie->getLieu()->setVille($ville);

            if($sortie->isIsPublished()){
                $sortie->setEtat($etatRepository->findOneBy(['libelle' =>'Ouverte']));
            }else{
                $sortie->setEtat($etatRepository->findOneBy(['libelle' =>'Créée']));
            }

            $site = $this->getUser()->getSite();
            $sortie->setSite($site);

            if($isEditMode) {
                $user = $this->getUser();
                $organisateur = $sortie->getOrganisateur();
                $isOrganisateur = ($user === $organisateur);

                $isPubliee = $sortie->isIsPublished();

                if (!$isOrganisateur || !$isPubliee) {
                    return $this->redirectToRoute('app_sortie_new');
                }
            }

            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sortie/new.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
            'editMode' => $isEditMode
        ]);
    }

    #[Route('/{id}', name: '_delete', methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {

        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->request->get('_token'))) {
            $entityManager->remove($sortie);
            $entityManager->flush();
            $this->addFlash('success', 'La sortie a été supprimée');
        } else {
            $this->addFlash('error', 'Token CSRF invalide');
        }

        return $this->redirectToRoute('app_sortie_index');
    }

    #[Route('/all', name: '_all')]
    public function showAll(SortieRepository $sortieRepository ):Response
    {
        $sorties = $sortieRepository->findAll();


        return $this->render('sortie/all-sorties.html.twig',[
            'sorties'=>$sorties,
        ]);
    }
}
