<?php

namespace App\Model;

use App\Entity\Author;

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
