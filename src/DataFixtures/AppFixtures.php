<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Post;
use App\Model\PostCategory;
use App\Model\PostStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;

/**
 * 500 fake posts generated from a fixed seed, so every load produces the same data.
 */
final class AppFixtures extends Fixture
{
    private const COUNT = 500;
    private const SEED = 42;

    private const AUTHORS = [
        ['Leanne Graham', 'Bret', 'Sincere@april.biz'],
        ['Ervin Howell', 'Antonette', 'Shanna@melissa.tv'],
        ['Clementine Bauch', 'Samantha', 'Nathan@yesenia.net'],
        ['Patricia Lebsack', 'Karianne', 'Julianne.OConner@kory.org'],
        ['Chelsey Dietrich', 'Kamren', 'Lucio_Hettinger@annie.ca'],
        ['Mrs. Dennis Schulist', 'Leopoldo_Corkery', 'Karley_Dach@jasper.info'],
        ['Kurtis Weissnat', 'Elwyn.Skiles', 'Telly.Hoeger@billy.biz'],
        ['Nicholas Runolfsdottir V', 'Maxime_Nienow', 'Sherwood@rosamond.me'],
        ['Glenna Reichert', 'Delphine', 'Chaim_McDermott@dana.io'],
        ['Clementina DuBuque', 'Moriah.Stanton', 'Rey.Padberg@karina.biz'],
    ];

    private const TITLE_PATTERNS = [
        'Getting started with %s',
        '%s in production: lessons learned',
        'Why we migrated to %s',
        'A deep dive into %s',
        '10 tips for a better %s setup',
        'Debugging %s like a pro',
        'What is new in %s this month',
        'Building an admin dashboard with %s',
        'Testing %s the right way',
        'How %s cut our response time in half',
    ];

    private const SUBJECTS = [
        'Symfony UX', 'Twig Components', 'Live Components', 'Stimulus', 'Turbo', 'Vite', 'Tailwind CSS',
        'Doctrine', 'Messenger', 'API Platform', 'FrankenPHP', 'PHPUnit', 'PHPStan', 'Rector', 'Symfony Reprise',
    ];

    private const WORDS = [
        'lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur', 'adipiscing', 'elit', 'sed', 'do', 'eiusmod',
        'tempor', 'incididunt', 'ut', 'labore', 'et', 'dolore', 'magna', 'aliqua', 'enim', 'ad', 'minim', 'veniam',
        'quis', 'nostrud', 'exercitation', 'ullamco', 'laboris', 'nisi', 'aliquip', 'ex', 'ea', 'commodo', 'consequat',
    ];

    public function load(ObjectManager $manager): void
    {
        $random = new Randomizer(new Xoshiro256StarStar(self::SEED));
        $now = new \DateTimeImmutable('2026-10-01 09:00:00');

        $authors = [];
        foreach (self::AUTHORS as $i => [$name, $username, $email]) {
            $manager->persist($authors[] = new Author($name, $username, strtolower($email), 0 === $i % 3));
        }

        $titles = [];
        for ($id = 1; $id <= self::COUNT; ++$id) {
            $status = match (true) {
                ($roll = $random->getInt(1, 100)) <= 60 => PostStatus::Published,
                $roll <= 80 => PostStatus::Draft,
                $roll <= 90 => PostStatus::Scheduled,
                default => PostStatus::Archived,
            };
            $views = \in_array($status, [PostStatus::Draft, PostStatus::Scheduled], true) ? 0 : $random->getInt(40, 48000);
            $words = array_map(static fn (int $key): string => self::WORDS[$key], $random->pickArrayKeys(self::WORDS, $random->getInt(14, 24)));
            $offset = PostStatus::Scheduled === $status ? $random->getInt(1, 60) : -$random->getInt(0, 720);
            $title = \sprintf(self::TITLE_PATTERNS[$random->getInt(0, \count(self::TITLE_PATTERNS) - 1)], self::SUBJECTS[$random->getInt(0, \count(self::SUBJECTS) - 1)]);
            // titles are unique, so repeats get numbered
            $titles[$title] = ($titles[$title] ?? 0) + 1;

            $manager->persist(new Post()
                ->setTitle(1 === $titles[$title] ? $title : \sprintf('%s (part %d)', $title, $titles[$title]))
                ->setExcerpt(ucfirst(implode(' ', $random->shuffleArray($words))).'.')
                ->setAuthor($authors[$random->getInt(0, \count($authors) - 1)])
                ->setCategory(PostCategory::cases()[$random->getInt(0, \count(PostCategory::cases()) - 1)])
                ->setStatus($status)
                ->setTags(array_map(static fn (int $key): string => Post::TAGS[$key], $random->pickArrayKeys(Post::TAGS, $random->getInt(1, 3))))
                ->setViews($views)
                ->setComments(0 === $views ? 0 : $random->getInt(0, intdiv($views, 150) + 2))
                ->setReadingTime($random->getInt(2, 18))
                ->setFeatured(1 === $random->getInt(1, 8))
                ->setPublishedAt($now->modify(\sprintf('%+d days %+d minutes', $offset, -$random->getInt(0, 1439)))));
        }

        $manager->flush();
    }
}
