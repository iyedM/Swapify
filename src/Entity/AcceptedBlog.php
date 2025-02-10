<?php

namespace App\Entity;

use App\Repository\AcceptedBlogRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AcceptedBlogRepository::class)]
class AcceptedBlog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Contenu = null;

    #[ORM\Column(length: 255)]
    private ?string $Titre = null;

    #[ORM\Column]
    private ?int $rate = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    /**
     * @var Collection<int, Commentaire>
     */
    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'accBlog')]
    private Collection $listeCommentaires;

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

    public function getRate(): ?int
    {
        return $this->rate;
    }

    public function setRate(int $rate): static
    {
        $this->rate = $rate;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

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
            $listeCommentaire->setAccBlog($this);
        }

        return $this;
    }

    public function removeListeCommentaire(Commentaire $listeCommentaire): static
    {
        if ($this->listeCommentaires->removeElement($listeCommentaire)) {
            // set the owning side to null (unless already changed)
            if ($listeCommentaire->getAccBlog() === $this) {
                $listeCommentaire->setAccBlog(null);
            }
        }

        return $this;
    }

}
