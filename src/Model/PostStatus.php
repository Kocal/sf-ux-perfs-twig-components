<?php

namespace App\Model;

enum PostStatus: string
{
    case Published = 'published';
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Published => 'Published',
            self::Draft => 'Draft',
            self::Scheduled => 'Scheduled',
            self::Archived => 'Archived',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Published => 'lucide:circle-check',
            self::Draft => 'lucide:pencil-line',
            self::Scheduled => 'lucide:clock',
            self::Archived => 'lucide:archive',
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Published => 'default',
            self::Draft => 'secondary',
            self::Scheduled => 'outline',
            self::Archived => 'destructive',
        };
    }
}
