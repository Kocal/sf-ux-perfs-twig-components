<?php

namespace App\Entity;

use App\Repository\AuthorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuthorRepository::class)]
class Author
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'author')]
    private Collection $posts;

    public function __construct(
        #[ORM\Column(length: 100)]
        private string $name,
        #[ORM\Column(length: 50, unique: true)]
        private string $username,
        #[ORM\Column(length: 180)]
        private string $email,
        #[ORM\Column]
        private bool $online = false,
    ) {
        $this->posts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function isOnline(): bool
    {
        return $this->online;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function getInitials(): string
    {
        $words = array_values(array_filter(explode(' ', $this->name), static fn (string $word): bool => !str_ends_with($word, '.')));

        return strtoupper(substr($words[0], 0, 1).substr($words[1] ?? '', 0, 1));
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
