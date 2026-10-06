<?php

namespace App\Model;

final readonly class AuthorStats
{
    public function __construct(
        public Author $author,
        public int $posts,
        public int $published,
        public int $views,
    ) {
    }
}
