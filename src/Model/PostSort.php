<?php

namespace App\Model;

enum PostSort: string
{
    case Newest = 'newest';
    case Oldest = 'oldest';
    case MostViewed = 'most-viewed';
    case MostCommented = 'most-commented';
    case Title = 'title';

    public function label(): string
    {
        return match ($this) {
            self::Newest => 'Newest first',
            self::Oldest => 'Oldest first',
            self::MostViewed => 'Most viewed',
            self::MostCommented => 'Most commented',
            self::Title => 'Title (A-Z)',
        };
    }
}
