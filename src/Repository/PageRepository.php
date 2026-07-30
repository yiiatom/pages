<?php

declare(strict_types=1);

namespace Atom\Pages\Repository;

use Atom\Pages\Data\PageDataReader;
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
}
