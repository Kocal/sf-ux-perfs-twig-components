<?php

namespace App\Controller;

use App\Model\PostCategory;
use App\Model\PostSort;
use App\Model\PostStatus;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    public const PER_PAGE_CHOICES = [10, 25, 50, 100, 250, 500];

    #[Route('/', name: 'app_dashboard', methods: ['GET'])]
    public function __invoke(
        PostRepository $posts,
        #[MapQueryParameter] string $q = '',
        #[MapQueryParameter] string $status = '',
        #[MapQueryParameter] string $category = '',
        #[MapQueryParameter] PostSort $sort = PostSort::Newest,
        #[MapQueryParameter] bool $featured = false,
        #[MapQueryParameter(options: ['min_range' => 1])] int $page = 1,
        #[MapQueryParameter] int $perPage = 25,
    ): Response {
        $filters = [
            'q' => trim($q),
            'status' => PostStatus::tryFrom($status),
            'category' => PostCategory::tryFrom($category),
            'sort' => $sort,
            'featured' => $featured,
            'perPage' => \in_array($perPage, self::PER_PAGE_CHOICES, true) ? $perPage : 25,
        ];

        return $this->render('dashboard/index.html.twig', [
            'page' => $posts->paginate($filters['q'], $filters['status'], $filters['category'], $sort, $featured, $page, $filters['perPage']),
            'filters' => $filters,
            'per_page_choices' => self::PER_PAGE_CHOICES,
            'status_counts' => $posts->countByStatus(),
            'category_counts' => $posts->countByCategory(),
            'total_posts' => \count($posts->findAll()),
            'total_views' => $posts->sumViews(),
            'total_comments' => $posts->sumComments(),
            'author_stats' => $posts->findAuthorStats(),
            'recent_posts' => $posts->findRecent(6),
        ]);
    }
}
