<?php

namespace App\Model;

final readonly class PostPage
{
    /**
     * @param list<Post> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $perPage,
    ) {
    }

    public function pageCount(): int
    {
        return max(1, (int) ceil($this->total / $this->perPage));
    }

    public function firstIndex(): int
    {
        return 0 === $this->total ? 0 : ($this->page - 1) * $this->perPage + 1;
    }

    public function lastIndex(): int
    {
        return min($this->total, $this->page * $this->perPage);
    }

    /**
     * Page numbers to display, with null standing for an ellipsis.
     *
     * @return list<int|null>
     */
    public function window(int $around = 1): array
    {
        $last = $this->pageCount();
        $pages = [];
        $previous = 0;
        foreach (range(1, $last) as $page) {
            if (1 !== $page && $last !== $page && abs($page - $this->page) > $around) {
                continue;
            }
            if ($page - $previous > 1) {
                $pages[] = null;
            }
            $pages[] = $page;
            $previous = $page;
        }

        return $pages;
    }
}
