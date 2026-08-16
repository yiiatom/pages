<?php

declare(strict_types=1);

namespace Atom\Pages\Repository;

use Atom\Helper\ReadableDataProxy;
use Atom\Pages\Data\PageDataReader;
use Atom\Pages\Entity\Page;
use Atom\Pages\Entity\PageStatus;
use Atom\Pages\Mapper\PageMapper;
use Yiisoft\Data\Db\QueryDataReader;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Iterable\IterableDataReader;
use Yiisoft\Data\Reader\ReadableDataInterface;
use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class PageRepository
{
    public function __construct(
        private ConnectionInterface $connection,
        private PageMapper $mapper,
    ) {}

    public function save(Page|array $pages): void
    {
        $pages = is_array($pages) ? $pages : [$pages];

        $this->connection->transaction(function () use ($pages): void {
            foreach ($pages as $page) {
                $old = $this->findOneByUuid($page->getUuid());

                if ($old !== null) {
                    $this->update($page, $old);
                } else {
                    $this->insert($page);
                }
            }
        });
    }

    private function insert(Page $entity): void
    {
        $position = $this->getNextPosition($entity->getParentUuid());
        $entity->setPosition($position);

        $row = $this->mapper->mapEntityToRow($entity);
        $this->connection->createCommand()->insert('{{%page}}', $row)->execute();
    }

    private function update(Page $entity, Page $old): void
    {
        if ($entity->getParentUuid() !== $old->getParentUuid()) {
            $position = $this->getNextPosition($entity->getParentUuid());
            $entity->setPosition($position);
        }

        $oldRow = $this->mapper->mapEntityToRow($old);
        $row = $this->mapper->mapEntityToRow($entity);

        $this->connection->createCommand()->update('{{%page}}', $row, ['uuid' => $entity->getUuid()])->execute();

        if ($entity->isDeleted() && !$old->isDeleted()) {
            $this->connection->createCommand()
                ->update('{{%page}}', [
                    'deleted_at' => $row['deleted_at'],
                ], '_path LIKE :path', null, [
                    ':path' => $oldRow['_path'] . Page::INTERNAL_PATH_SEPARATOR . '%',
                ])->execute();
        }

        if ($row['_path'] !== $oldRow['_path']) {
            $this->connection->createCommand(
                'UPDATE {{%page}}
                SET [[_path]] = REPLACE([[_path]], :oldPath, :newPath),
                    [[depth]] = [[depth]] + :depthDiff,
                    [[_location]] = REPLACE([[_location]], :oldLocation, :newLocation)
                WHERE _path LIKE :likePath',
            )->bindValues([
                ':oldPath' => $oldRow['_path'] . Page::INTERNAL_PATH_SEPARATOR,
                ':newPath' => $row['_path'] . Page::INTERNAL_PATH_SEPARATOR,
                ':depthDiff' => $row['depth'] - $oldRow['depth'],
                ':oldLocation' => $oldRow['_location'] . Page::INTERNAL_LOCATION_SEPARATOR,
                ':newLocation' => $row['_location'] . Page::INTERNAL_LOCATION_SEPARATOR,
                ':likePath' => $oldRow['_path'] . Page::INTERNAL_PATH_SEPARATOR . '%',
            ])->execute();
        }
    }

    public function purgeDeleted(): int
    {
        return $this->connection->createCommand()
            ->delete('{{%page}}', ['not', ['deleted_at' => null]])
            ->execute();
    }

    private function createEntity(?array $row): ?Page
    {
        if ($row === null) {
            return null;
        }

        return $this->mapper->mapRowToEntity($row);
    }

    public function getSummaryStats(): array
    {
        $stats = [
            'total' => 0,
            'published' => 0,
            'draft' => 0,
            'trash' => 0,
        ];

        $rows = $this->connection
            ->select(['status', 'COUNT(*) as count'])
            ->from('{{%page}}')
            ->where(['deleted_at' => null])
            ->groupBy('status')
            ->all();

        foreach ($rows as $row) {
            $count = (int) $row['count'];
            $stats['total'] += $count;

            if ($row['status'] == PageStatus::PUBLISHED->value) {
                $stats['published'] += $count;
            } elseif ($row['status'] == PageStatus::DRAFT->value) {
                $stats['draft'] += $count;
            }
        }

        $stats['trash'] = $this->getDeletedCount();

        return $stats;
    }

    public function getDeletedCount(): int
    {
        return $this->connection->createQuery()
            ->from('{{%page}}')
            ->where(['not', ['deleted_at' => null]])
            ->count();
    }

    public function exists(string $uuid): bool
    {
        return $this->connection->createQuery()
            ->from('{{%page}}')
            ->where(['uuid' => $uuid])
            ->exists();
    }

    public function existsBySlug(
        string $slug,
        ?string $parentUuid,
        ?string $excludeUuid = null,
    ): bool
    {
        if ($parentUuid === '') {
            $parentUuid = null;
        }

        $query = $this->connection->createQuery()
            ->from('{{%page}}')
            ->where(['slug' => $slug, 'parent_uuid' => $parentUuid]);

        $query->andWhere(['deleted_at' => null]);

        if ($excludeUuid) {
            $query->andWhere(['!=', 'uuid', $excludeUuid]);
        }

        return $query->exists();
    }

    public function findOneByUuid(string $uuid): ?Page
    {
        $query = $this->connection
            ->select()
            ->from('{{%page}}')
            ->where(['uuid' => $uuid]);

        return $this->createEntity($query->one());
    }

    public function findTreeAsDataReader(array $filters = []): ReadableDataInterface
    {
        $rows = $this->connection
            ->select()
            ->where(['deleted_at' => null])
            ->orderBy(['position' => SORT_ASC])
            ->from('{{%page}}')->all();

        $grouped = [];
        foreach ($rows as $row) {
            $parentUuid = $row['parent_uuid'] ?: 'root';
            $grouped[$parentUuid][] = $row;
        }

        $orderedRows = [];
        $buildTree = function (?string $parentUuid) use (&$grouped, &$orderedRows, &$buildTree): void {
            $key = $parentUuid ?? 'root';
            if (!isset($grouped[$key])) {
                return;
            }
            foreach ($grouped[$key] as $row) {
                $orderedRows[] = $row;
                $buildTree((string) $row['uuid']);
            }
        };
        $buildTree(null);

        $reader = new IterableDataReader($orderedRows);
        $dataReader = new PageDataReader($reader, $this->mapper);

        return new ReadableDataProxy($dataReader);
    }

    public function findAllDeletedAsDataReader(): DataReaderInterface
    {
        $query = $this->connection
            ->select()
            ->from('{{%page}}')
            ->where(['not', ['deleted_at' => null]])
            ->orderBy([
                'deleted_at' => SORT_DESC,
                '_path' => SORT_ASC,
            ]);

        $reader = new QueryDataReader($query);

        return new PageDataReader($reader, $this->mapper);
    }

    public function findAllParents(Page $entity): array
    {
        $row = $this->mapper->mapEntityToRow($entity);

        $parts = explode(Page::INTERNAL_PATH_SEPARATOR, $row['_path']);
        array_shift($parts);
        array_pop($parts);

        $paths = [];
        $path = '';
        foreach ($parts as $part) {
            $path .= Page::INTERNAL_PATH_SEPARATOR . $part;
            $paths[] = $path;
        }

        $rows = $this->connection
            ->select()
            ->from('{{%page}}')
            ->where(['_path' => $paths])
            ->orderBy(['_path' => SORT_ASC])
            ->all();

        $items = [];
        foreach ($rows as $row) {
            $items[] = $this->createEntity($row);
        }

        return $items;
    }

    private function getNextPosition(?string $parentUuid): int
    {
        if ($parentUuid === '') {
            $parentUuid = null;
        }

        $query = $this->connection
            ->select(['MAX(position) as position'])
            ->from('{{%page}}')
            ->where(['parent_uuid' => $parentUuid]);

        $max = (int) $query->scalar();

        return $max + 1;
    }

    public function updatePositions(array $positions): void
    {
        $this->connection->transaction(function () use ($positions): void {
            foreach ($positions as $uuid => $position) {
                $this->connection->createCommand()
                    ->update('{{%page}}', ['position' => $position], ['uuid' => $uuid])
                    ->execute();
            }
        });
    }
}
