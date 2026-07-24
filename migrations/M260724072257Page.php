<?php

declare(strict_types=1);

use Atom\Pages\Entity\PageStatus;
use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;
use Yiisoft\Db\Migration\TransactionalMigrationInterface;
use Yiisoft\Db\Schema\Column\ColumnBuilder;

final class M260724072257Page implements RevertibleMigrationInterface, TransactionalMigrationInterface
{
    private const PAGE_TABLE = '{{%page}}';

    public function up(MigrationBuilder $b): void
    {
        $b->createTable(self::PAGE_TABLE, [
            'uuid' => ColumnBuilder::string(36)->notNull(),
            'title' => ColumnBuilder::string(255)->notNull(),
            'slug' => ColumnBuilder::string(255)->notNull(),
            'path' => ColumnBuilder::string(1024)->notNull(),
            'parent_uuid' => ColumnBuilder::string(36),
            'position' => ColumnBuilder::integer()->notNull()->defaultValue(0),
            'content' => ColumnBuilder::text()->notNull(),
            'status' => ColumnBuilder::string(32)->notNull()->defaultValue(PageStatus::DRAFT->value),
            'created_at' => ColumnBuilder::datetime()->notNull(),
            'updated_at' => ColumnBuilder::datetime()->notNull(),
            'deleted_at' => ColumnBuilder::datetime(),
        ]);

        $b->addPrimaryKey(self::PAGE_TABLE, 'uuid', 'uuid');
        
        $b->addForeignKey(
            self::PAGE_TABLE, 
            'fk_page_parent', 
            'parent_uuid', 
            self::PAGE_TABLE, 
            'uuid', 
            'RESTRICT', 
            'CASCADE'
        );

        $b->execute('CREATE INDEX `path` ON ' . self::PAGE_TABLE . ' (`path`(255))');

        $b->execute('CREATE INDEX `path_position` ON ' . self::PAGE_TABLE . ' (`path`(255), `position`)');

        $b->createIndex(self::PAGE_TABLE, 'parent_uuid_slug', ['parent_uuid', 'slug', 'deleted_at'], 'UNIQUE');
    }

    public function down(MigrationBuilder $b): void
    {
        $b->dropTable(self::PAGE_TABLE);
    }
}
