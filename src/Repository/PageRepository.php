<?php

declare(strict_types=1);

namespace Atom\Pages\Repository;

use Atom\Pages\Data\PageDataReader;
use Atom\Pages\Entity\Page;
use Atom\Pages\Entity\PageStatus;
use Atom\Pages\Mapper\PageMapper;
use Yiisoft\Data\Reader\Iterable\IterableDataReader;
use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class PageRepository
{
    public function __construct(
        private ConnectionInterface $connection,
        private PageMapper $mapper,
    ) {}

    public function save(Page $entity): void
    {
        $old = $this->findOneByUuid($entity->getUuid());

        if ($old !== null) {
            $this->update($entity, $old);
        } else {
            $this->insert($entity);
        }
    }

    private function insert(Page $entity): void
    {
        $this->connection->transaction(function () use ($entity): void {
            $position = $this->getNextPosition($entity->getParentUuid());
            $entity->setPosition($position);

            $row = $this->mapper->mapEntityToRow($entity);
            $this->connection->createCommand()->insert('{{%page}}', $row)->execute();
        });
    }

    private function update(Page $entity, Page $old): void
    {
        $this->connection->transaction(function () use ($entity): void {
            $row = $this->mapper->mapEntityToRow($entity);
            $this->connection->createCommand()->update('{{%page}}', $row, ['uuid' => $entity->getUuid()])->execute();

            if ($entity->getPath() !== $old->getPath()) {
                $this->connection->createCommand(
                    'UPDATE {{%page}}
                    SET path = REPLACE(path, :oldPath, :newPath),
                        depth = depth + :depthDiff
                    WHERE path LIKE :likePath',
                )->bindValues([
                    ':oldPath' => $old->getPath(),
                    ':newPath' => $entity->getPath(),
                    ':depthDiff' => $entity->getDepth() - $old->getDepth(),
                    ':likePath' => $old->getPath() . '/%',
                ])->execute();
            }
        });
    }

    private function createEntity(?array $row): ?Page
    {
        if ($row === null) {
            return null;
        }

        return $this->mapper->mapRowToEntity($row);
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

        $query->andWhere(['!=', 'status', PageStatus::DELETED->value]);

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

    public function getTreeAsDataReader(array $filters = []): PageDataReader
    {
        $rows = $this->connection
            ->select()
            ->where(['!=', 'status', PageStatus::DELETED->value])
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

        return new PageDataReader($reader, $this->mapper);
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
}
