<?php

namespace App\Twig\Components\App;

use App\Model\PostCategory;
use App\Model\PostPage;
use App\Model\PostSort;
use App\Model\PostStatus;
use App\Repository\PostRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Metadata\UrlMapping;

/**
 * The posts table: filters bound to the URL, row selection and bulk actions.
 */
#[AsLiveComponent]
final class PostsTable
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    public const PER_PAGE_CHOICES = [10, 25, 50, 100, 250, 500];

    #[LiveProp(writable: true, url: new UrlMapping(as: 'q'), onUpdated: 'resetPagination')]
    public string $query = '';

    #[LiveProp(writable: true, url: true, onUpdated: 'resetPagination')]
    public ?PostStatus $status = null;

    #[LiveProp(writable: true, url: true, onUpdated: 'resetPagination')]
    public ?PostCategory $category = null;

    #[LiveProp(writable: true, url: true, onUpdated: 'resetPagination')]
    public PostSort $sort = PostSort::Newest;

    #[LiveProp(writable: true, url: true, onUpdated: 'resetPagination')]
    public bool $featured = false;

    #[LiveProp(writable: true, url: true, onUpdated: 'onPerPageUpdated')]
    public int $perPage = 25;

    #[LiveProp(url: true)]
    public int $page = 1;

    /**
     * @var list<int>
     */
    #[LiveProp(writable: true)]
    public array $selected = [];

    private ?PostPage $postPage = null;

    public function __construct(
        private readonly PostRepository $posts,
    ) {
    }

    public function resetPagination(): void
    {
        $this->page = 1;
        $this->selected = [];
    }

    public function onPerPageUpdated(int $previous): void
    {
        // the browser sends the select value back as a string, even when it did not change
        if ($previous !== $this->perPage) {
            $this->resetPagination();
        }
    }

    public function getPostPage(): PostPage
    {
        $perPage = \in_array($this->perPage, self::PER_PAGE_CHOICES, true) ? $this->perPage : 25;

        return $this->postPage ??= $this->posts->paginate(trim($this->query), $this->status, $this->category, $this->sort, $this->featured, $this->page, $perPage);
    }

    /**
     * @return array<string, int>
     */
    public function getStatusCounts(): array
    {
        return $this->posts->countByStatus();
    }

    /**
     * @return array<string, int>
     */
    public function getCategoryCounts(): array
    {
        return $this->posts->countByCategory();
    }

    public function isAllSelected(): bool
    {
        $ids = array_map(static fn ($post): int => $post->getId(), $this->getPostPage()->items);

        return [] !== $ids && [] === array_diff($ids, $this->selected);
    }

    #[LiveAction]
    public function goToPage(#[LiveArg] int $page): void
    {
        $this->page = max(1, $page);
        $this->selected = [];
    }

    #[LiveAction]
    public function sortBy(#[LiveArg] PostSort $sort): void
    {
        $this->sort = $sort;
        $this->resetPagination();
    }

    #[LiveAction]
    public function toggleAll(): void
    {
        $this->selected = $this->isAllSelected() ? [] : array_map(static fn ($post): int => $post->getId(), $this->getPostPage()->items);
    }

    #[LiveAction]
    #[IsGranted('ROLE_EDITOR')]
    public function bulkSetStatus(#[LiveArg] PostStatus $status): void
    {
        $this->posts->updateStatusByIds($this->selectedIds(), $status);
        $this->afterBulkAction();
    }

    #[LiveAction]
    #[IsGranted('ROLE_EDITOR')]
    public function bulkDelete(): void
    {
        $this->posts->deleteByIds($this->selectedIds());
        $this->afterBulkAction();
    }

    #[LiveListener('post:saved')]
    public function refresh(): void
    {
    }

    /**
     * @return list<int>
     */
    private function selectedIds(): array
    {
        return array_values(array_map('intval', $this->selected));
    }

    private function afterBulkAction(): void
    {
        $this->selected = [];
        $this->postPage = null;
        $this->emit('posts:changed');
    }
}
