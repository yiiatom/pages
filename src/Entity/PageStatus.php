<?php

declare(strict_types=1);

namespace Atom\Pages\Entity;

enum PageStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case DELETED = 'deleted';

    public function getLabel(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PUBLISHED => 'Published',
            self::DELETED => 'Deleted',
        };
    }

    public function getCssClass(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-warning text-dark',
            self::PUBLISHED => 'bg-success text-white',
            self::DELETED => 'bg-danger text-white',
        };
    }
}
