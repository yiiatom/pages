<?php

declare(strict_types=1);

namespace Atom\Pages\Entity;

use DateTimeImmutable;

final class Page
{
    public function __construct(
        private string $uuid,
        private string $title,
        private string $slug,
        private string $path,
        private int $depth,
        private ?string $parentUuid,
        private int $position,
        private string $content,
        private PageStatus $status,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
        private ?DateTimeImmutable $deletedAt,
    ) {}

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getDepth(): int
    {
        return $this->depth;
    }

    public function getParentUuid(): ?string
    {
        return $this->parentUuid;
    }

    public function getStatus(): PageStatus
    {
        return $this->status;
    }
}
