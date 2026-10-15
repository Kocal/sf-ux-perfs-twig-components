<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard', methods: ['GET'])]
    public function __invoke(PostRepository $posts): Response
    {
        $statusCounts = $posts->countByStatus();

        return $this->render('dashboard/index.html.twig', [
            'status_counts' => $statusCounts,
            'category_counts' => $posts->countByCategory(),
            'total_posts' => array_sum($statusCounts),
            'total_views' => $posts->sumViews(),
            'author_stats' => $posts->findAuthorStats(),
            'recent_posts' => $posts->findRecent(6),
        ]);
    }
}
