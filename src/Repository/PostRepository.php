<?php

namespace App\Repository;

use App\Entity\Author;
use App\Entity\Post;
use App\Model\AuthorStats;
use App\Model\PostCategory;
use App\Model\PostPage;
use App\Model\PostSort;
use App\Model\PostStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
final class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function paginate(string $query = '', ?PostStatus $status = null, ?PostCategory $category = null, PostSort $sort = PostSort::Newest, bool $featuredOnly = false, int $page = 1, int $perPage = 25): PostPage
    {
        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p.author', 'a');

        if ('' !== $query) {
            $qb->andWhere('LOWER(p.title) LIKE :query OR LOWER(a.name) LIKE :query')
                ->setParameter('query', '%'.mb_strtolower($query).'%');
        }
        if (null !== $status) {
            $qb->andWhere('p.status = :status')->setParameter('status', $status);
        }
        if (null !== $category) {
            $qb->andWhere('p.category = :category')->setParameter('category', $category);
        }
        if ($featuredOnly) {
            $qb->andWhere('p.featured = true');
        }

        $total = (int) (clone $qb)->select('COUNT(p.id)')->getQuery()->getSingleScalarResult();
        $page = max(1, min($page, max(1, (int) ceil($total / $perPage))));

        $items = $this->sorted($qb->addSelect('a'), $sort)
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();

        return new PostPage($items, $total, $page, $perPage);
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
        return $this->countBy('status', array_column(PostStatus::cases(), 'value'));
    }

    /**
     * @return array<string, int> post count, keyed by category value
     */
    public function countByCategory(): array
    {
        return $this->countBy('category', array_column(PostCategory::cases(), 'value'));
    }

    public function sumViews(): int
    {
        return (int) $this->createQueryBuilder('p')->select('SUM(p.views)')->getQuery()->getSingleScalarResult();
    }

    public function sumComments(): int
    {
        return (int) $this->createQueryBuilder('p')->select('SUM(p.comments)')->getQuery()->getSingleScalarResult();
    }

    /**
     * @return list<AuthorStats> sorted by views, most viewed first
     */
    public function findAuthorStats(): array
    {
        $rows = $this->getEntityManager()->createQueryBuilder()
            ->select('a AS author', 'COUNT(p.id) AS posts', 'SUM(CASE WHEN p.status = :published THEN 1 ELSE 0 END) AS published', 'SUM(p.views) AS views')
            ->from(Author::class, 'a')
            ->innerJoin('a.posts', 'p')
            ->groupBy('a.id')
            ->orderBy('views', 'DESC')
            ->addOrderBy('a.id', 'ASC')
            ->setParameter('published', PostStatus::Published)
            ->getQuery()
            ->getResult();

        return array_map(static fn (array $row): AuthorStats => new AuthorStats($row['author'], (int) $row['posts'], (int) $row['published'], (int) $row['views']), $rows);
    }

    /**
     * @param list<int> $ids
     */
    public function deleteByIds(array $ids): int
    {
        return $this->createQueryBuilder('p')
            ->delete()
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }

    /**
     * @param list<int> $ids
     */
    public function updateStatusByIds(array $ids, PostStatus $status): int
    {
        return $this->createQueryBuilder('p')
            ->update()
            ->set('p.status', ':status')
            ->where('p.id IN (:ids)')
            ->setParameter('status', $status)
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }

    private function sorted(QueryBuilder $qb, PostSort $sort): QueryBuilder
    {
        match ($sort) {
            PostSort::Newest => $qb->orderBy('p.publishedAt', 'DESC'),
            PostSort::Oldest => $qb->orderBy('p.publishedAt', 'ASC'),
            PostSort::MostViewed => $qb->orderBy('p.views', 'DESC'),
            PostSort::MostCommented => $qb->orderBy('p.comments', 'DESC'),
            PostSort::Title => $qb->addSelect('LOWER(p.title) AS HIDDEN lowerTitle')->orderBy('lowerTitle', 'ASC'),
        };

        return $qb->addOrderBy('p.id', 'ASC');
    }

    /**
     * @param list<string> $keys
     *
     * @return array<string, int>
     */
    private function countBy(string $field, array $keys): array
    {
        $counts = array_fill_keys($keys, 0);
        $rows = $this->createQueryBuilder('p')
            ->select("p.$field AS field", 'COUNT(p.id) AS total')
            ->groupBy("p.$field")
            ->getQuery()
            ->getResult();

        foreach ($rows as $row) {
            $counts[$row['field'] instanceof \BackedEnum ? $row['field']->value : $row['field']] = (int) $row['total'];
        }

        return $counts;
    }
}
