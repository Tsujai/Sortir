<?php

namespace App\Controller;

use App\Entity\Site;
use App\Form\SearchType;
use App\Form\SiteType;
use App\Repository\SiteRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SiteController extends AbstractController
{
    #[Route('/site', name: 'app_site')]
    #[Route('/site/update/{id}', name: 'app_update_site', requirements: ['id' => '\d+'])]
    public function index(SiteRepository $siteRepository, EntityManagerInterface $entityManager,Request $request): Response
    {

        $nom = new Site();
        $site = new Site();

        $sites = $siteRepository->findAll();

        $form = $this->createForm(SearchType::class);
        $formUpdate = $this->createForm(SiteType::class, $nom);
        $formCreate = $this->createForm(SiteType::class, $site);

        $formCreate->handleRequest($request);
        $form->handleRequest($request);
        $formUpdate->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $datas = $form->getData();
            if (!empty($datas['query'])) {
                $sites = $siteRepository->findBySearch($datas['query']);
            }
        }
        if ($formCreate->isSubmitted() && $formCreate->isValid()){

            $entityManager->persist($site);
            $entityManager->flush();
            $this->addFlash('success', 'Ajout du site valider');

            return $this->redirectToRoute('app_site');
        }
        if ($formUpdate->isSubmitted() && $formUpdate->isValid()){

            $entityManager->persist($nom);
            $entityManager->flush();
            $this->addFlash('success', 'Le nom du site a été modifiée');

            return $this->redirectToRoute('app_site');
        }


        return $this->render('site/index.html.twig', [
            'sites' => $sites,
            'form' => $form,
            'formSite'=>$formCreate,
            'formUpdate' => $formUpdate,

        ]);


    }
    #[Route('site/{id}', name: 'app_delete_site', methods: ['POST'])]
    public function delete(Site $site, EntityManagerInterface $entityManager, Request $request) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $site->getId(),$request->get('_token'))) {
            $entityManager->remove($site);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_site', [], Response::HTTP_SEE_OTHER);
    }




}
