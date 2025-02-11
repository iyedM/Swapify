<?php

namespace App\Entity;

use App\Enum\EtatEnum;
use App\Repository\BlogRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs as EventLifecycleEventArgs;

#[ORM\Entity(repositoryClass: BlogRepository::class)]
class Blog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Contenu = null;

    #[ORM\Column(length: 255)]
    private ?string $Titre = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $rate = 0;
    
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $rateCount = 0; // Count of ratings
    

    

    /**
     * @var Collection<int, Commentaire>
     */
    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'blog')]
    private Collection $listeCommentaires;

    #[ORM\Column(length: 255, enumType: EtatEnum::class, options: ["default" => "enAttente"])]
    private EtatEnum $statut = EtatEnum::enAttente;

    #[ORM\ManyToOne(inversedBy: 'blogs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user;
    

    public function __construct()
    {
        $this->listeCommentaires = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenu(): ?string
    {
        return $this->Contenu;
    }

    public function setContenu(string $Contenu): static
    {
        $this->Contenu = $Contenu;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->Titre;
    }

    public function setTitre(string $Titre): static
    {
        $this->Titre = $Titre;

        return $this;
    }

    public function getRate(): float
    {
        return $this->rateCount > 0 ? $this->rate / $this->rateCount : 0;
    }
    
    public function addRate(int $rating): self
    {
        if ($rating >= 0 && $rating <= 5) {
            $this->rate += $rating;
            $this->rateCount++;
        }
        return $this;
    }
    public function setRate(int $rate): static
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getListeCommentaires(): Collection
    {
        return $this->listeCommentaires;
    }

    public function addListeCommentaire(Commentaire $listeCommentaire): static
    {
        if (!$this->listeCommentaires->contains($listeCommentaire)) {
            $this->listeCommentaires->add($listeCommentaire);
            $listeCommentaire->setBlog($this);
        }

        return $this;
    }

    public function removeListeCommentaire(Commentaire $listeCommentaire): static
    {
        if ($this->listeCommentaires->removeElement($listeCommentaire)) {
            // set the owning side to null (unless already changed)
            if ($listeCommentaire->getBlog() === $this) {
                $listeCommentaire->setBlog(null);
            }
        }

        return $this;
    }

    public function getStatut(): EtatEnum
    {
        return $this->statut;
    }

    public function setStatut(EtatEnum $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }


}
