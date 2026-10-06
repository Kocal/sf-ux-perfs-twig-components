<?php

namespace App\Repository;

use App\Model\Author;
use App\Model\AuthorStats;
use App\Model\Post;
use App\Model\PostCategory;
use App\Model\PostPage;
use App\Model\PostSort;
use App\Model\PostStatus;
use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;

/**
 * In-memory list of fake posts, generated from a fixed seed so every request renders the same data.
 */
final class PostRepository
{
    private const AUTHORS = [
        ['Leanne Graham', 'Bret', 'Sincere@april.biz'],
        ['Ervin Howell', 'Antonette', 'Shanna@melissa.tv'],
        ['Clementine Bauch', 'Samantha', 'Nathan@yesenia.net'],
        ['Patricia Lebsack', 'Karianne', 'Julianne.OConner@kory.org'],
        ['Chelsey Dietrich', 'Kamren', 'Lucio_Hettinger@annie.ca'],
        ['Mrs. Dennis Schulist', 'Leopoldo_Corkery', 'Karley_Dach@jasper.info'],
        ['Kurtis Weissnat', 'Elwyn.Skiles', 'Telly.Hoeger@billy.biz'],
        ['Nicholas Runolfsdottir V', 'Maxime_Nienow', 'Sherwood@rosamond.me'],
        ['Glenna Reichert', 'Delphine', 'Chaim_McDermott@dana.io'],
        ['Clementina DuBuque', 'Moriah.Stanton', 'Rey.Padberg@karina.biz'],
    ];

    private const TITLE_PATTERNS = [
        'Getting started with %s',
        '%s in production: lessons learned',
        'Why we migrated to %s',
        'A deep dive into %s',
        '10 tips for a better %s setup',
        'Debugging %s like a pro',
        'What is new in %s this month',
        'Building an admin dashboard with %s',
        'Testing %s the right way',
        'How %s cut our response time in half',
    ];

    private const SUBJECTS = [
        'Symfony UX', 'Twig Components', 'Live Components', 'Stimulus', 'Turbo', 'Vite', 'Tailwind CSS',
        'Doctrine', 'Messenger', 'API Platform', 'FrankenPHP', 'PHPUnit', 'PHPStan', 'Rector', 'Symfony Reprise',
    ];

    private const TAGS = ['php', 'twig', 'javascript', 'css', 'symfony', 'devops', 'testing', 'ux', 'api', 'tooling'];

    private const WORDS = [
        'lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur', 'adipiscing', 'elit', 'sed', 'do', 'eiusmod',
        'tempor', 'incididunt', 'ut', 'labore', 'et', 'dolore', 'magna', 'aliqua', 'enim', 'ad', 'minim', 'veniam',
        'quis', 'nostrud', 'exercitation', 'ullamco', 'laboris', 'nisi', 'aliquip', 'ex', 'ea', 'commodo', 'consequat',
    ];

    /** @var list<Post>|null */
    private ?array $posts = null;

    public function __construct(
        private readonly int $count = 500,
        private readonly int $seed = 42,
    ) {
    }

    /**
     * @return list<Post>
     */
    public function findAll(): array
    {
        return $this->posts ??= $this->generate();
    }

    public function paginate(string $query = '', ?PostStatus $status = null, ?PostCategory $category = null, PostSort $sort = PostSort::Newest, bool $featuredOnly = false, int $page = 1, int $perPage = 25): PostPage
    {
        $posts = array_values(array_filter(
            $this->findAll(),
            static fn (Post $post): bool => ('' === $query || false !== stripos($post->title, $query) || false !== stripos($post->author->name, $query))
                && (null === $status || $post->status === $status)
                && (null === $category || $post->category === $category)
                && (!$featuredOnly || $post->featured),
        ));

        usort($posts, match ($sort) {
            PostSort::Newest => static fn (Post $a, Post $b): int => $b->publishedAt <=> $a->publishedAt,
            PostSort::Oldest => static fn (Post $a, Post $b): int => $a->publishedAt <=> $b->publishedAt,
            PostSort::MostViewed => static fn (Post $a, Post $b): int => $b->views <=> $a->views,
            PostSort::MostCommented => static fn (Post $a, Post $b): int => $b->comments <=> $a->comments,
            PostSort::Title => static fn (Post $a, Post $b): int => strcasecmp($a->title, $b->title),
        });

        $page = max(1, min($page, max(1, (int) ceil(\count($posts) / $perPage))));

        return new PostPage(\array_slice($posts, ($page - 1) * $perPage, $perPage), \count($posts), $page, $perPage);
    }

    /**
     * @return list<Post>
     */
    public function findRecent(int $limit): array
    {
        return $this->paginate(status: PostStatus::Published, perPage: $limit)->items;
    }

    /**
     * @return array<string, int> post count, keyed by status value
     */
    public function countByStatus(): array
    {
        $counts = array_fill_keys(array_column(PostStatus::cases(), 'value'), 0);
        foreach ($this->findAll() as $post) {
            ++$counts[$post->status->value];
        }

        return $counts;
    }

    /**
     * @return array<string, int> post count, keyed by category value
     */
    public function countByCategory(): array
    {
        $counts = array_fill_keys(array_column(PostCategory::cases(), 'value'), 0);
        foreach ($this->findAll() as $post) {
            ++$counts[$post->category->value];
        }

        return $counts;
    }

    public function sumViews(): int
    {
        return array_sum(array_map(static fn (Post $post): int => $post->views, $this->findAll()));
    }

    public function sumComments(): int
    {
        return array_sum(array_map(static fn (Post $post): int => $post->comments, $this->findAll()));
    }

    /**
     * @return list<AuthorStats> sorted by views, most viewed first
     */
    public function findAuthorStats(): array
    {
        $stats = [];
        foreach ($this->findAll() as $post) {
            $current = $stats[$post->author->id] ?? new AuthorStats($post->author, 0, 0, 0);
            $stats[$post->author->id] = new AuthorStats(
                $post->author,
                $current->posts + 1,
                $current->published + (PostStatus::Published === $post->status ? 1 : 0),
                $current->views + $post->views,
            );
        }

        usort($stats, static fn (AuthorStats $a, AuthorStats $b): int => $b->views <=> $a->views);

        return $stats;
    }

    /**
     * @return list<Post>
     */
    private function generate(): array
    {
        $random = new Randomizer(new Xoshiro256StarStar($this->seed));
        $now = new \DateTimeImmutable('2026-10-01 09:00:00');

        $authors = [];
        foreach (self::AUTHORS as $i => [$name, $username, $email]) {
            $authors[] = new Author($i + 1, $name, $username, strtolower($email), 0 === $i % 3);
        }

        $posts = [];
        for ($id = 1; $id <= $this->count; ++$id) {
            $status = match (true) {
                ($roll = $random->getInt(1, 100)) <= 60 => PostStatus::Published,
                $roll <= 80 => PostStatus::Draft,
                $roll <= 90 => PostStatus::Scheduled,
                default => PostStatus::Archived,
            };
            $views = \in_array($status, [PostStatus::Draft, PostStatus::Scheduled], true) ? 0 : $random->getInt(40, 48000);
            $words = array_map(static fn (int $key): string => self::WORDS[$key], $random->pickArrayKeys(self::WORDS, $random->getInt(14, 24)));
            $offset = PostStatus::Scheduled === $status ? $random->getInt(1, 60) : -$random->getInt(0, 720);

            $posts[] = new Post(
                id: $id,
                title: \sprintf(self::TITLE_PATTERNS[$random->getInt(0, \count(self::TITLE_PATTERNS) - 1)], self::SUBJECTS[$random->getInt(0, \count(self::SUBJECTS) - 1)]),
                excerpt: ucfirst(implode(' ', $random->shuffleArray($words))).'.',
                author: $authors[$random->getInt(0, \count($authors) - 1)],
                category: PostCategory::cases()[$random->getInt(0, \count(PostCategory::cases()) - 1)],
                status: $status,
                tags: array_map(static fn (int $key): string => self::TAGS[$key], $random->pickArrayKeys(self::TAGS, $random->getInt(1, 3))),
                views: $views,
                comments: 0 === $views ? 0 : $random->getInt(0, intdiv($views, 150) + 2),
                readingTime: $random->getInt(2, 18),
                featured: 1 === $random->getInt(1, 8),
                publishedAt: $now->modify(\sprintf('%+d days %+d minutes', $offset, -$random->getInt(0, 1439))),
            );
        }

        return $posts;
    }
}
