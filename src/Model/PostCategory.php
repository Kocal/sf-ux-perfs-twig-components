<?php

namespace App\Model;

enum PostCategory: string
{
    case Tutorial = 'tutorial';
    case News = 'news';
    case Release = 'release';
    case Community = 'community';
    case Performance = 'performance';
    case Security = 'security';

    public function label(): string
    {
        return match ($this) {
            self::Tutorial => 'Tutorial',
            self::News => 'News',
            self::Release => 'Release',
            self::Community => 'Community',
            self::Performance => 'Performance',
            self::Security => 'Security',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Tutorial => 'lucide:graduation-cap',
            self::News => 'lucide:newspaper',
            self::Release => 'lucide:rocket',
            self::Community => 'lucide:users',
            self::Performance => 'lucide:gauge',
            self::Security => 'lucide:shield-check',
        };
    }
}
