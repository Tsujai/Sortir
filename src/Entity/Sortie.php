<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SortieRepository::class)]
#[UniqueEntity(fields: ['nom', 'dateHeureDebut', 'lieu'], message: 'Sortie Déjà prévue', errorPath: 'nom')]
class Sortie
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\GreaterThanOrEqual('tomorrow UTC',message: 'Le début de l\'activité doit être postérieur à la date du jour')]
    private ?\DateTime $dateHeureDebut = null;

    #[ORM\Column(length: 4)]
    #[Assert\Length(max: 4,maxMessage: 'La durée maximale est de 9999 minutes.')]
    #[Assert\Type(type: 'numeric',message: 'Chiffres uniquement')]
    private ?string $duree = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\LessThanOrEqual(propertyPath: 'dateHeureDebut',message: 'La date limite d\'inscription doit être antérieur au début de l\'activité')]
    private ?\DateTime $dateLimiteInscription = null;

    #[ORM\Column(length: 3)]
    #[Assert\Length(max: 3,maxMessage: 'Le nombre maximum de participants est 999.')]
    #[Assert\Type(type: 'numeric',message: 'Chiffres uniquement')]
    private ?int $nbInscriptionsMax = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $infosSortie = null;

    #[ORM\ManyToMany(targetEntity: Participant::class, mappedBy: 'sorties')]
    private Collection $participants;

    #[ORM\ManyToOne(inversedBy: 'organisateur')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participant $organisateur = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Site $site = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etat $etat = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Lieu $lieu = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isPublished = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cancelMotif = null;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    /**
     * @param Etat|null $etat
     */
    public function setEtat(?Etat $etat): static
    {
        $this->etat = $etat;
        return $this;

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDateHeureDebut(): ?\DateTime
    {
        return $this->dateHeureDebut;
    }
//    #[ORM\PrePersist]
    public function setDateHeureDebut(\DateTime $dateHeureDebut): static
    {
        $this->dateHeureDebut = $dateHeureDebut;

        return $this;
    }

    public function getDuree(): ?string
    {
        return $this->duree;
    }

    public function setDuree(string $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDateLimiteInscription(): ?\DateTime
    {
        return $this->dateLimiteInscription;
    }

    public function setDateLimiteInscription(\DateTime $dateLimiteInscription): static
    {
        $this->dateLimiteInscription = $dateLimiteInscription;

        return $this;
    }

    public function getNbInscriptionsMax(): ?int
    {
        return $this->nbInscriptionsMax;
    }

    public function setNbInscriptionsMax(int $nbInscriptionsMax): static
    {
        $this->nbInscriptionsMax = $nbInscriptionsMax;

        return $this;
    }

    public function getInfosSortie(): ?string
    {
        return $this->infosSortie;
    }

    public function setInfosSortie(?string $infosSortie): static
    {
        $this->infosSortie = $infosSortie;

        return $this;
    }

    /**
     * @return Collection<int, Participant>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participant $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
            $participant->addSortie($this);
        }

        return $this;
    }

    public function removeParticipant(Participant $participant): static
    {
        if ($this->participants->removeElement($participant)) {
            $participant->removeSortie($this);
        }

        return $this;
    }

    public function getOrganisateur(): ?Participant
    {
        return $this->organisateur;
    }

    public function setOrganisateur(?Participant $organisateur): static
    {
        $this->organisateur = $organisateur;

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): static
    {
        $this->site = $site;

        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function getLieu(): ?Lieu
    {
        return $this->lieu;
    }

    public function setLieu(?Lieu $lieu): static
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function isIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(?bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getCancelMotif(): ?string
    {
        return $this->cancelMotif;
    }

    public function setCancelMotif(?string $cancelMotif): static
    {
        $this->cancelMotif = $cancelMotif;

        return $this;
    }
}
