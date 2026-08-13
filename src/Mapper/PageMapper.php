<?php

declare(strict_types=1);

namespace Atom\Pages\Mapper;

use Atom\Pages\Entity\Page;
use Atom\Pages\Entity\PageStatus;
use Closure;
use DateTimeImmutable;
use Yiisoft\Hydrator\HydratorInterface;

final class PageMapper
{
    public function __construct(
        private HydratorInterface $hydrator,
    ) {}

    public function mapRowToEntity(array $row): Page
    {
        $data = [
            'uuid' => $row['uuid'],
            'title' => $row['title'],
            'slug' => $row['slug'],
            '_path' => $row['_path'],
            'depth' => $row['depth'],
            'parentUuid' => $row['parent_uuid'],
            'position' => $row['position'],
            '_location' => $row['_location'],
            'content' => $row['content'],
            'status' => PageStatus::from($row['status']),
            'createdAt' => new DateTimeImmutable($row['created_at']),
            'updatedAt' => new DateTimeImmutable($row['updated_at']),
            'deletedAt' => $row['deleted_at'] ? new DateTimeImmutable($row['deleted_at']) : null,
        ];

        return $this->hydrator->create(Page::class, $data);
    }

    public function mapEntityToRow(Page $entity): array
    {
        $extractor = function (): array {
            return [
                'uuid' => $this->uuid,
                'title' => $this->title,
                'slug' => $this->slug,
                '_path' => $this->_path,
                'depth' => $this->depth,
                'parent_uuid' => $this->parentUuid,
                'position' => $this->position,
                '_location' => $this->_location,
                'content' => $this->content,
                'status' => $this->status->value,
                'created_at' => $this->createdAt,
                'updated_at' => $this->updatedAt,
                'deleted_at' => $this->deletedAt,
            ];
        };

        $extractorClosure = Closure::bind($extractor, $entity, Page::class);

        return $extractorClosure();
    }
}
