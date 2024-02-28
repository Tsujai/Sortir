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
use Symfony\Component\Security\Http\Attribute\IsGranted;
#[IsGranted('ROLE_ADMIN')]
class SiteController extends AbstractController
{

    #[Route('/site/home', name: 'app_site_home')]
    #[Route('/site/update/{id}', name: 'app_update_site', requirements: ['id' => '\d+'])]
    public function index(?int $id, SiteRepository $siteRepository, EntityManagerInterface $entityManager,Request $request): Response
    {

        $site = new Site();

        $sites = $siteRepository->findAll();

        $form = $this->createForm(SearchType::class);
        $formSite = $this->createForm(SiteType::class, $site);

        $formSite->handleRequest($request);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $datas = $form->getData();
            if (!empty($datas['query'])) {
                $sites = $siteRepository->findBySearch($datas['query']);
            }
        }
       // création d'un site
        if ($formSite->isSubmitted() && $formSite->isValid()){

            $entityManager->persist($site);
            $entityManager->flush();
            $this->addFlash('success', 'Ajout du site validé');

            return $this->redirectToRoute('app_site_home');
        }
        //mise à jour d'un site
        if ($request->get('mode') === 'update'){

            $site = $siteRepository->find($id);
            $site->setNom($request->get('nom'));
            $entityManager->persist($site);
            $entityManager->flush();
            $this->addFlash('success', 'Le nom du site a été modifié');

            return $this->redirectToRoute('app_site_home');
        }


        return $this->render('site/index.html.twig', [
            'sites' => $sites,
            'form' => $form,
            'formSite'=>$formSite,
            'formUpdate' => $formSite,

        ]);


    }
    #[Route('site/delete/{id}', name: 'app_delete_site', methods: ['POST'])]
    public function delete(Site $site, EntityManagerInterface $entityManager, Request $request) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $site->getId(),$request->get('_token'))) {
            if (!empty($site->getSorties())){
                foreach ($site->getSorties() as $sorty){
                    $sorty->setSite(null);
                    $entityManager->persist($sorty);
                }
            }
            $entityManager->remove($site);
            $entityManager->flush();
            $this->addFlash('success', 'Le site à été supprimé avec succes');
        }

        return $this->redirectToRoute('app_site_home', [], Response::HTTP_SEE_OTHER);
    }

}
