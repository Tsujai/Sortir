<?php

namespace App\Controller;

use App\Form\FormCreateType;
use App\Form\NouvelleSortieType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SortieController extends AbstractController
{
    #[Route('/nouvelleSortie', name: 'app_nouvSortie')]
    public function index(): Response
    {
        $form = $this->createForm(NouvelleSortieType::class);
        return $this->render('sortie/nouvelleSortie.html.twig', [
            'form' => $form,
        ]);
    }
}
