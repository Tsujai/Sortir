<?php

namespace App\Controller;


use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\CancelFormType;
use App\Form\ListeSortiesType;
use App\Form\NewLieuType;
use App\Form\NouvelleSortieType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
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
    public function details(int $id, SortieRepository $sortieRepository, EtatRepository $etatRepository, EntityManagerInterface $entityManager): Response
    {
        $sortie = $sortieRepository->find($id);

        $this->gestionEtat($etatRepository, $sortie);

        $entityManager->persist($sortie);
        $entityManager->flush();

        return $this->render('sortie/details.html.twig', [
            'sortie' => $sortie,
        ]);
    }


    #[Route('/new', name: '_new', methods: ['GET', 'POST'])]
    #[Route('/edit/{id}', name: '_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function new(?Sortie $sortie, Request $request, EntityManagerInterface $entityManager, EtatRepository $etatRepository, MailerInterface $mailer): Response
    {
        $isEditMode = $sortie ? true : false;
        if (!$isEditMode) {
            $sortie = new Sortie();
        }else{
            $sortieAvantModif = clone $sortie;
        }
        $user = $this->getUser();
        $this->isGranted('ROLE_USER');
        $form = $this->createForm(NouvelleSortieType::class, $sortie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (!$isEditMode) {
                $sortie->setOrganisateur($this->getUser());
                $this->sendEmailModifSortie('mails/sortie-creee.html.twig', 'Création de la sortie ' . $sortie->getNom(), $user, $mailer,$sortie);
            }

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

                if ($sortie->getParticipants() != null){
                    $participants = $sortieAvantModif->getParticipants();
                    foreach ($participants as $participant){
                        $this->sendEmailModifSortie('mails/sortie-modifiee.html.twig', 'Modification de la sortie ' . $sortieAvantModif->getNom(), $participant, $mailer,$sortie);
                    }
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
    public function inscription(Sortie $sortie, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $participant = $this->getUser();
        $participantMax = $sortie->getNbInscriptionsMax();
        $nombreInscrit = $sortie->getParticipants()->count();

        if (($nombreInscrit < $participantMax) && ($sortie->getEtat()->getLibelle() == 'Ouverte')){

            $sortie->addParticipant($participant);

            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'inscription prise en compte');

            $this->sendEmailModifSortie('mails/sortie-inscrit.html.twig', 'Inscription à la sortie ' . $sortie->getNom(), $participant, $mailer,$sortie);

        } else {
            $this->addFlash('warning', 'Inscription non valide');
        }
        return $this->redirectToRoute('app_sortie_all');
    }

    #[Route('/desistement/{id}', name: '_desistement', methods: ['GET'])]
    public function desistement(Sortie $sortie, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $participant = $this->getUser();

        if ($participant && ($sortie->getEtat()->getLibelle() == 'Ouverte')) {

            $sortie->removeParticipant($participant);

            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Désinscription réussie.');

            $this->sendEmailModifSortie('mails/sortie-desinscrit.html.twig', 'Desinscription à la sortie ' . $sortie->getNom(), $participant, $mailer,$sortie);


        } else {
            $this->addFlash('warning', 'Désinscription non valide');
        }
        return $this->redirectToRoute('app_sortie_all');
    }


    #[Route('/delete/{id}', name: '_delete', methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $user = $this->getUser();
        $sortieSupprimee = clone $sortie;
        $isOrganisateur = (($this->getUser())->getUserIdentifier() == ($sortie->getOrganisateur())->getEmail());

        if ($isOrganisateur || ($this->isCsrfTokenValid('delete' . $sortie->getId(), $request->request->get('_token')))) {
            $entityManager->remove($sortie);
            $entityManager->flush();
            $this->addFlash('success', 'La sortie a été supprimée');
            $this->sendEmailModifSortie('mails/sortie-supprimee.html.twig', 'Suppression de la sortie ' . $sortieSupprimee->getNom(), $user, $mailer,$sortie);
        } else {
            $this->addFlash('error', 'Token CSRF invalide');
        }
        return $this->redirectToRoute('app_sortie_all');
    }

    #[Route('/all', name: '_all')]
    public function showAll(EtatRepository $etatRepository, SortieRepository $sortieRepository, Request $request, EntityManagerInterface $entityManager):Response
    {
        $sorties = $sortieRepository->findAll();
        $userConnected = $this->getUser();
        $form = $this->createForm(ListeSortiesType::class);
        $form->handleRequest($request);

        $now = new \DateTime();

        if ($form->isSubmitted() && $form->isValid()){

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

        $sortiesAffichees = new ArrayCollection();


        foreach ($sorties as $sortie){

            $this->gestionEtat($etatRepository, $sortie);

            $duree = new \DateInterval('PT' . $sortie->getDuree() . 'M');
            $archivage = new \DateInterval('P1M');
            $dateDebut = $sortie->getDateHeureDebut();
            $dateFin = clone $dateDebut;
            $dateFin->add($duree);
            $dateArchivage = clone $dateFin;
            $dateArchivage->add($archivage);

            $entityManager->persist($sortie);

            if ($sortie->getEtat()->getLibelle() == 'Créée' && ($userConnected == $sortie->getOrganisateur() || $this->isGranted('ROLE_ADMIN'))){
                $sortiesAffichees->add($sortie);
            }else if ($sortie->getEtat()->getLibelle() != 'Créée' && $dateArchivage > $now){
                $sortiesAffichees->add($sortie);
            }
        }
        $entityManager->flush();

        return $this->render('sortie/all-sorties.html.twig', [
            'sorties' => $sortiesAffichees,
            'userConnected' => $userConnected,
            'form' => $form,
        ]);
    }

    #[Route('/publier/{id}', name: '_publier', requirements: ['id' => '\d+'])]
    public function publier(Sortie $sortie, EtatRepository $etatRepository, EntityManagerInterface $entityManager, MailerInterface $mailer):Response
    {
        $user = $this->getUser();
        if ($sortie->getEtat()->getLibelle() == 'Créée') {
            $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Ouverte']));

            $entityManager->persist($sortie);
            $entityManager->flush();
            $this->addFlash('success', 'La sortie a été publiée');
            $this->sendEmailModifSortie('mails/sortie-publiee.html.twig', 'Publication de la sortie ' . $sortie->getNom(), $user, $mailer,$sortie);

        } else {
            $this->addFlash('error', 'Action interdite !');
        }

        return $this->redirectToRoute('app_sortie_all');
    }

    #[Route('/annuler/{id}', name: '_annuler', requirements: ['id' => '\d+'])]
    public function annuler(EtatRepository $etatRepository, EntityManagerInterface $entityManager, SortieRepository $sortieRepository, MailerInterface $mailer, Request $request): Response
    {

        $form = $this->createForm(CancelFormType::class);
        $form->handleRequest($request);

        $sortie = $sortieRepository->find($request->get('id'));

        if ($form->isSubmitted() && $form->isValid()){
            $sortie->setCancelMotif($form->get('motif')->getData());
            if ($sortie->getEtat()->getLibelle() == 'Ouverte') {
                $participants = $sortieRepository->find($sortie->getId())->getParticipants();
                if ($participants != null){
                    foreach ($participants as $participant) {
                        $this->sendEmailModifSortie('mails/sortie-annulee.html.twig', 'Annulation de la sortie ' . $sortie->getNom(), $participant, $mailer,$sortie);
                        $sortie->removeParticipant($participant);
                    }
                }
            }
            $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Annulée']));
            $entityManager->persist($sortie);

            $entityManager->flush();

            return $this->redirectToRoute('app_sortie_all');
        }

        return $this->render('sortie/motif-cancel.html.twig',[
            'form'=>$form,
            'sortie'=>$sortie,
        ]);
    }

    #[Route('/lieu/new', name: '_new_lieu')]
    public function newLieu(Request $request, EntityManagerInterface $entityManager): Response
    {
        $id = $request->get('id');

        $newLieu = new Lieu();
        $form = $this->createForm(NewLieuType::class,$newLieu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $entityManager->persist($newLieu);
            $entityManager->flush();
            if ($id){
                return $this->redirectToRoute('app_sortie_edit',['id'=>$id]);
            }else{
                return $this->redirectToRoute('app_sortie_new');
            }

        }
        return $this->render('lieu/new-lieu.html.twig',[
            'form'=>$form,
        ]);
    }

    private function sendEmailModifSortie(string $emailTemplate, string $emailSubject, Participant $participant, MailerInterface $mailer, Sortie $sortie) : void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@sortir.com', 'infos-sortir.com'))
            ->to($participant->getEmail())
            ->subject($emailSubject)
            ->htmlTemplate($emailTemplate)
            ->context([
                'sortie' => $sortie,
            ]);
        ;

        $mailer->send($email);
    }

    private function gestionEtat(EtatRepository $etatRepository, Sortie $sortie) : void
    {
        $duree = new \DateInterval('PT' . $sortie->getDuree() . 'M');
        $dateDebut = $sortie->getDateHeureDebut();
        $dateFin = clone $dateDebut;
        $dateFin->add($duree);
        $now = new \DateTime();

        if (($dateFin < $now) && ($sortie->getEtat()->getLibelle() != 'Annulée')){
            $sortie->setEtat($etatRepository->findOneBy(['libelle'=>'Passée']));
        }else if (($now > $sortie->getDateHeureDebut()) && ($now <= $dateFin) && $sortie->isIsPublished()){
            $sortie->setEtat($etatRepository->findOneBy(['libelle'=>'Activité en cours']));
        }
    }


}
