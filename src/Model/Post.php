<?php

namespace App\Model;

final readonly class Post
{
    /**
     * @param list<string> $tags
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $excerpt,
        public Author $author,
        public PostCategory $category,
        public PostStatus $status,
        public array $tags,
        public int $views,
        public int $comments,
        public int $readingTime,
        public bool $featured,
        public \DateTimeImmutable $publishedAt,
    ) {
    }
}
