<?php

declare(strict_types=1);

namespace Atom\Pages\Data;

use Generator;
use Atom\Pages\Entity\Page;
use Atom\Pages\Mapper\PageMapper;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\ReadableDataInterface;

class PageDataReader implements ReadableDataInterface
{
    /** @var Page[] */
    private array $data = [];

    public function __construct(
        private DataReaderInterface $dataReader,
        private PageMapper $mapper,
    ) {
        foreach ($dataReader->read() as $row) {
            $this->data[$row['uuid']] = $row;
        }
    }

    final public function read(): Generator
    {
        foreach ($this->data as $row) {
            yield $this->mapper->mapRowToEntity($row);
        }
    }

    final public function readOne(): Page|null
    {
        $row = $this->dataReader->readOne();
        return $this->mapper->mapRowToEntity($row);
    }
}
