<?php

declare(strict_types=1);

namespace Atom\Pages\Entity;

enum PageStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';

    public function getLabel(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PUBLISHED => 'Published',
        };
    }

    public function getCssClass(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-warning text-dark',
            self::PUBLISHED => 'bg-success text-white',
        };
    }
}
