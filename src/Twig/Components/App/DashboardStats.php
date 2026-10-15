<?php

namespace App\Twig\Components\App;

use App\Repository\PostRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\DefaultActionTrait;

/**
 * The four stat cards on top of the dashboard, refreshed when posts change.
 */
#[AsLiveComponent]
final class DashboardStats
{
    use DefaultActionTrait;

    /**
     * @var array<string, int>|null
     */
    private ?array $statusCounts = null;

    public function __construct(
        private readonly PostRepository $posts,
    ) {
    }

    /**
     * @return array<string, int>
     */
    public function getStatusCounts(): array
    {
        return $this->statusCounts ??= $this->posts->countByStatus();
    }

    public function getTotalPosts(): int
    {
        return array_sum($this->getStatusCounts());
    }

    public function getTotalViews(): int
    {
        return $this->posts->sumViews();
    }

    public function getTotalComments(): int
    {
        return $this->posts->sumComments();
    }

    #[LiveListener('post:saved')]
    #[LiveListener('posts:changed')]
    public function refresh(): void
    {
    }
}
