<?php

declare(strict_types=1);

namespace Atom\Pages\Entity;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

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

    public static function create(
        string $title = '',
        string $slug = '',
        string $content = '',
        PageStatus $status = PageStatus::DRAFT,
        ?Page $parent = null,
    ): self {
        $date = new DateTimeImmutable;
        $parentPath = $parent ? $parent->getPath() : '';
        return new self(
            uuid: Uuid::uuid7()->toString(),
            title: $title,
            slug: $slug,
            path: $parentPath . '/' . $slug,
            depth: $parent ? $parent->getDepth() + 1 : 0,
            parentUuid: $parent ? $parent->getUuid() : null,
            position: 0,
            content: $content,
            status: $status,
            createdAt: $createdAt ?? $date,
            updatedAt: $updatedAt ?? $date,
            deletedAt: null,
        );
    }

    public function update(
        string $title = '',
        string $slug = '',
        string $content = '',
        PageStatus $status = PageStatus::DRAFT,
        ?Page $parent = null,
    ): void
    {
        $this->title = $title;
        $this->content = $content;
        $this->status = $status;

        if ($this->slug !== $slug || $this->parentUuid !== $parent?->getUuid()) {
            $this->slug = $slug;
            $this->parentUuid = $parent?->getUuid();

            if ($parent === null) {
                $this->depth = 0;
                $this->path = '/' . $slug;
            } else {
                $this->depth = $parent->getDepth() + 1;
                $this->path = $parent->getPath() . '/' . $slug;
            }
        }

        $this->updatedAt = new DateTimeImmutable;
    }

    public function delete(): void
    {
        $this->status = PageStatus::DELETED;
        $this->deletedAt = new DateTimeImmutable;
        $this->updatedAt = new DateTimeImmutable;
    }

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

    public function setPosition(int $value): self
    {
        $this->position = $value;
        $this->updatedAt = new DateTimeImmutable;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getStatus(): PageStatus
    {
        return $this->status;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }
}
