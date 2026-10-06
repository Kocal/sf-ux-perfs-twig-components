<?php

namespace App\Model;

final readonly class Author
{
    public function __construct(
        public int $id,
        public string $name,
        public string $username,
        public string $email,
        public bool $online,
    ) {
    }

    public function initials(): string
    {
        $words = array_values(array_filter(explode(' ', $this->name), static fn (string $word): bool => !str_ends_with($word, '.')));

        return strtoupper(substr($words[0], 0, 1).substr($words[1] ?? '', 0, 1));
    }
}
