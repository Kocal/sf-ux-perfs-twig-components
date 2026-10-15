<?php

namespace App\Entity;

use App\Model\PostCategory;
use App\Model\PostStatus;
use App\Repository\PostRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\Index(columns: ['status'])]
#[ORM\Index(columns: ['category'])]
#[ORM\Index(columns: ['published_at'])]
#[UniqueEntity('title', message: 'Another post already uses this title.')]
class Post
{
    public const TAGS = ['php', 'twig', 'javascript', 'css', 'symfony', 'devops', 'testing', 'ux', 'api', 'tooling'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 10, max: 120)]
    private ?string $title = null;

    #[ORM\Column(length: 280)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 280)]
    private ?string $excerpt = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Author $author = null;

    #[ORM\Column(length: 20, enumType: PostCategory::class)]
    #[Assert\NotNull]
    private ?PostCategory $category = null;

    #[ORM\Column(length: 20, enumType: PostStatus::class)]
    #[Assert\NotNull]
    private PostStatus $status = PostStatus::Draft;

    /**
     * @var list<string>
     */
    #[ORM\Column(type: Types::JSON)]
    #[Assert\Count(min: 1, max: 3)]
    #[Assert\All([new Assert\Choice(choices: self::TAGS)])]
    private array $tags = [];

    #[ORM\Column]
    private int $views = 0;

    #[ORM\Column]
    private int $comments = 0;

    #[ORM\Column]
    #[Assert\Range(min: 1, max: 60)]
    private int $readingTime = 5;

    #[ORM\Column]
    private bool $featured = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getExcerpt(): ?string
    {
        return $this->excerpt;
    }

    public function setExcerpt(?string $excerpt): static
    {
        $this->excerpt = $excerpt;

        return $this;
    }

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(?Author $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getCategory(): ?PostCategory
    {
        return $this->category;
    }

    public function setCategory(?PostCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getStatus(): PostStatus
    {
        return $this->status;
    }

    public function setStatus(PostStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param list<string> $tags
     */
    public function setTags(array $tags): static
    {
        $this->tags = array_values($tags);

        return $this;
    }

    public function getViews(): int
    {
        return $this->views;
    }

    public function setViews(int $views): static
    {
        $this->views = $views;

        return $this;
    }

    public function getComments(): int
    {
        return $this->comments;
    }

    public function setComments(int $comments): static
    {
        $this->comments = $comments;

        return $this;
    }

    public function getReadingTime(): int
    {
        return $this->readingTime;
    }

    public function setReadingTime(int $readingTime): static
    {
        $this->readingTime = $readingTime;

        return $this;
    }

    public function isFeatured(): bool
    {
        return $this->featured;
    }

    public function setFeatured(bool $featured): static
    {
        $this->featured = $featured;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    #[Assert\Callback]
    public function validatePublicationDate(ExecutionContextInterface $context): void
    {
        if (PostStatus::Scheduled === $this->status && null === $this->publishedAt) {
            $context->buildViolation('A scheduled post needs a publication date.')
                ->atPath('publishedAt')
                ->addViolation();
        }
    }
}
