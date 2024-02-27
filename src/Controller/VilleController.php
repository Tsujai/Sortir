<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\Ville;
use App\Form\SearchType;
use App\Form\VilleType;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class VilleController extends AbstractController
{
    #[Route('/ville/home', name: 'app_ville_home')]
    #[Route('/ville/update/{id}', name: 'app_update_ville',requirements: ['id' => '\d+'])]
    public function index(?int $id, VilleRepository $villeRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $ville = new  Ville();

        $villes = $villeRepository->findAll();

        $form = $this->createForm(SearchType::class);
        $formVille = $this->createForm(VilleType::class, $ville);

        $form->handleRequest($request);
        $formVille->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $datas = $form->getData();
            if (!empty($datas['query'])) {
                $villes = $villeRepository->findBySearch($datas['query']);
            }
        }
        //création d'une ville
        if($formVille->isSubmitted() && $formVille->isValid()){

            $entityManager->persist($ville);
            $entityManager->flush();
            $this->addFlash('success', 'Ajout d\'une ville validée');

            return $this->redirectToRoute('app_ville_home');
        }
        //mise à jour d'une ville
        if ($request->get('mode') === 'update'){

            $ville = $villeRepository->find($id);
            $ville->setNom($request->get('nom'));
            $ville->setCodePostal($request->get('codePostal'));
            $entityManager->persist($ville);
            $entityManager->flush();

            $this->addFlash('success', 'La ville à été modifiée');

            return $this->redirectToRoute('app_ville_home');
        }

        return $this->render('ville/index.html.twig', [
            'villes' => $villes,
            'form'=> $form,
            'formVille' => $formVille,
            'formUpdate' => $formVille,
        ]);
    }
    #[Route('ville/delete/{id}', name: 'app_delete_ville',methods: ['POST'])]
    public function delete(Ville $ville, EntityManagerInterface $entityManager, Request $request) : Response
    {
        if($this->isCsrfTokenValid( 'delete' . $ville->getId(),$request->get('_token'))){
            if (!empty($ville->getLieux())){
                foreach ($ville->getLieux() as $lieux){
                    $lieux->setVille(null);
                    $entityManager->persist($lieux);
                }
            }
            $entityManager->remove($ville);
            $entityManager->flush();
            $this->addFlash('success', 'La ville à été supprimée avec success');
        }


        return $this->redirectToRoute('app_ville_home',[],Response::HTTP_SEE_OTHER);
    }
}
