<?php

namespace App\Controller;

use App\Form\SearchType;
use App\Repository\SiteRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SiteController extends AbstractController
{
    #[Route('/site', name: 'app_site')]
    public function index(SiteRepository $siteRepository, EntityManagerInterface $entityManager,Request $request): Response
    {

        $sites = $siteRepository->findAll();

         $form = $this->createForm(SearchType::class);

         $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $datas = $form->getData();
            if (!empty($datas['query'])) {
                $sites = $siteRepository->findBySearch($datas['query']);
            }
        }

        return $this->render('site/index.html.twig', [
            'sites' => $sites,
            'form' => $form,
        ]);

    }

}
