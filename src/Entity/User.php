<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Collection<int, Blog>
     */
    #[ORM\OneToMany(targetEntity: Blog::class, mappedBy: 'user')]
    private Collection $blogs;

    /**
     * @var Collection<int, Commentaire>
     */
    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'user')]
    private Collection $commentaires;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    public function __construct()
    {
        $this->blogs = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
    }

    // Add other fields as needed, e.g., username, first name, last name, etc.

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';  // This will ensure every user has the USER role
    
        // Optionally, check if this user is an admin and add the ROLE_ADMIN
        if (in_array('ROLE_ADMIN', $roles)) {
            $roles[] = 'ROLE_ADMIN';
        }
    
        return array_unique($roles);
    }
    

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Blog>
     */
    public function getBlogs(): Collection
    {
        return $this->blogs;
    }

    public function addBlog(Blog $blog): static
    {
        if (!$this->blogs->contains($blog)) {
            $this->blogs->add($blog);
            $blog->setUser($this);
        }

        return $this;
    }

    public function removeBlog(Blog $blog): static
    {
        if ($this->blogs->removeElement($blog)) {
            // set the owning side to null (unless already changed)
            if ($blog->getUser() === $this) {
                $blog->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): static
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires->add($commentaire);
            $commentaire->setUser($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): static
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getUser() === $this) {
                $commentaire->setUser(null);
            }
        }

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }
}


// namespace App\Entity;

// use App\Repository\UserRepository;
// use Doctrine\ORM\Mapping as ORM;
// use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
// use Symfony\Component\Security\Core\User\UserInterface;

// #[ORM\Entity(repositoryClass: UserRepository::class)]
// #[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
// class User implements UserInterface, PasswordAuthenticatedUserInterface
// {
//     #[ORM\Id]
//     #[ORM\GeneratedValue]
//     #[ORM\Column]
//     private ?int $id = null;

//     #[ORM\Column(length: 180)]
//     private ?string $username = null;

//     /**
//      * @var list<string> The user roles
//      */
//     #[ORM\Column]
//     private array $roles = [];

//     /**
//      * @var string The hashed password
//      */
//     #[ORM\Column]
//     private ?string $password = null;

//     public function getId(): ?int
//     {
//         return $this->id;
//     }

//     public function getUsername(): ?string
//     {
//         return $this->username;
//     }

//     public function setUsername(string $username): static
//     {
//         $this->username = $username;

//         return $this;
//     }

//     /**
//      * A visual identifier that represents this user.
//      *
//      * @see UserInterface
//      */
//     public function getUserIdentifier(): string
//     {
//         return (string) $this->username;
//     }

//     /**
//      * @see UserInterface
//      *
//      * @return list<string>
//      */
//     public function getRoles(): array
//     {
//         $roles = $this->roles;
//         // guarantee every user at least has ROLE_USER
//         $roles[] = 'ROLE_USER';

//         return array_unique($roles);
//     }

//     /**
//      * @param list<string> $roles
//      */
//     public function setRoles(array $roles): static
//     {
//         $this->roles = $roles;

//         return $this;
//     }

//     /**
//      * @see PasswordAuthenticatedUserInterface
//      */
//     public function getPassword(): ?string
//     {
//         return $this->password;
//     }

//     public function setPassword(string $password): static
//     {
//         $this->password = $password;

//         return $this;
//     }

//     /**
//      * @see UserInterface
//      */
//     public function eraseCredentials(): void
//     {
//         // If you store any temporary, sensitive data on the user, clear it here
//         // $this->plainPassword = null;
//     }
// }
