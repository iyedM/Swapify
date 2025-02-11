<?php

namespace App\Entity;

use App\Repository\CommentaireRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
class Commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $ContenuCmt = null;

    #[ORM\ManyToOne(inversedBy: 'listeCommentaires')]
    private ?Blog $blog = null;


    #[ORM\ManyToOne(inversedBy: 'commentaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user ;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenuCmt(): ?string
    {
        return $this->ContenuCmt;
    }

    public function setContenuCmt(string $ContenuCmt): static
    {
        $this->ContenuCmt = $ContenuCmt;

        return $this;
    }

    public function getBlog(): ?Blog
    {
        return $this->blog;
    }

    public function setBlog(?Blog $blog): static
    {
        $this->blog = $blog;

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
