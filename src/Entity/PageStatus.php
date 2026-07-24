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
}
