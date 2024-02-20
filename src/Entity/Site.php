<?php

namespace App\Entity;

use App\Repository\SiteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SiteRepository::class)]
class Site
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\OneToMany(targetEntity: Participant::class, mappedBy: 'Site')]
    private Collection $participantsAffilies;

    #[ORM\OneToMany(targetEntity: Sortie::class, mappedBy: 'Site', orphanRemoval: true)]
    private Collection $sorties;

    public function __construct()
    {
        $this->participantsAffilies = new ArrayCollection();
        $this->sorties = new ArrayCollection();
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

    /**
     * @return Collection<int, Participant>
     */
    public function getParticipantsAffilies(): Collection
    {
        return $this->participantsAffilies;
    }

    public function addParticipantsAffily(Participant $participantsAffily): static
    {
        if (!$this->participantsAffilies->contains($participantsAffily)) {
            $this->participantsAffilies->add($participantsAffily);
            $participantsAffily->setSite($this);
        }

        return $this;
    }

    public function removeParticipantsAffily(Participant $participantsAffily): static
    {
        if ($this->participantsAffilies->removeElement($participantsAffily)) {
            // set the owning side to null (unless already changed)
            if ($participantsAffily->getSite() === $this) {
                $participantsAffily->setSite(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addSorty(Sortie $sorty): static
    {
        if (!$this->sorties->contains($sorty)) {
            $this->sorties->add($sorty);
            $sorty->setSite($this);
        }

        return $this;
    }

    public function removeSorty(Sortie $sorty): static
    {
        if ($this->sorties->removeElement($sorty)) {
            // set the owning side to null (unless already changed)
            if ($sorty->getSite() === $this) {
                $sorty->setSite(null);
            }
        }

        return $this;
    }
}
