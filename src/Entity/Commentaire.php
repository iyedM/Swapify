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

    #[ORM\ManyToOne(inversedBy: 'listeCommentaires')]
    private ?AcceptedBlog $accBlog = null;



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

    public function getAccBlog(): ?AcceptedBlog
    {
        return $this->accBlog;
    }

    public function setAccBlog(?AcceptedBlog $accBlog): static
    {
        $this->accBlog = $accBlog;

        return $this;
    }


}
