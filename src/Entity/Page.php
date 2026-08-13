<?php

declare(strict_types=1);

namespace Atom\Pages\Entity;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

final class Page
{
    const PATH_SEPARATOR = '/';
    const LOCATION_SEPARATOR = "\u{2023}";
    const DEFAULT_SEPARATOR = '/';

    public function __construct(
        private string $uuid,
        private string $title,
        private string $slug,
        private string $_path,
        private int $depth,
        private ?string $parentUuid,
        private int $position,
        private string $_location,
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
        $parentPath = $parent ? $parent->_path : '';
        $parentLocation = $parent ? $parent->_location : '';
        return new self(
            uuid: Uuid::uuid7()->toString(),
            title: $title,
            slug: $slug,
            _path: $parentPath . self::PATH_SEPARATOR . $slug,
            depth: $parent ? $parent->getDepth() + 1 : 0,
            parentUuid: $parent ? $parent->getUuid() : null,
            position: 0,
            _location: $parentLocation . self::LOCATION_SEPARATOR . $title,
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
        $parentChanged = $this->parentUuid !== $parent?->getUuid();
        $this->parentUuid = $parent?->getUuid();

        if ($this->slug !== $slug || $parentChanged) {
            $this->slug = $slug;
            if ($parent === null) {
                $this->depth = 0;
                $this->_path = self::PATH_SEPARATOR . $slug;
            } else {
                $this->depth = $parent->getDepth() + 1;
                $this->_path = $parent->_path . self::PATH_SEPARATOR . $slug;
            }
        }

        if ($this->title !== $title || $parentChanged) {
            $this->title = $title;
            if ($parent === null) {
                $this->_location = self::LOCATION_SEPARATOR . $title;
            } else {
                $this->_location = $parent->_location . self::LOCATION_SEPARATOR . $title;
            }
        }

        $this->content = $content;
        $this->status = $status;

        $this->updatedAt = new DateTimeImmutable;
    }

    public function delete(): void
    {
        $this->deletedAt = new DateTimeImmutable;
        $this->updatedAt = new DateTimeImmutable;
    }

    public function restore(): void
    {
        $this->deletedAt = null;
        $this->updatedAt = new DateTimeImmutable;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
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

    public function getPath(string $separator = self::DEFAULT_SEPARATOR): string
    {
        return str_replace(self::PATH_SEPARATOR, $separator, $this->_path);
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

    public function getParentLocation(string $separator = self::DEFAULT_SEPARATOR): string
    {
        $parts = explode(self::LOCATION_SEPARATOR, $this->_location);
        array_shift($parts);
        array_pop($parts);

        return implode($separator, $parts);
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
