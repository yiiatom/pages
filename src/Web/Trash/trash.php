<?php

declare(strict_types=1);

use Atom\Pages\Entity\Page;
use Atom\Pages\Web\Shared\PagesAsset;
use Yiisoft\Html\Html;
use Yiisoft\Yii\DataView\GridView\Column\ActionButton;
use Yiisoft\Yii\DataView\GridView\GridView;
use Yiisoft\Yii\DataView\GridView\Column\ActionColumn;
use Yiisoft\Yii\DataView\GridView\Column\DataColumn;

use Yiisoft\Yii\DataView\Pagination\PaginationContext;
use Atom\Helper\DataViewUrlBridge;

$title = 'Trash';

$this->setTitle($title);

$assetManager->register(PagesAsset::class);

?>
<h1><?= Html::encode($title) ?></h1>

<?= GridView::widget()
    ->dataReader($dataReader)
    ->columns(
        new DataColumn(
            property: 'title',
            header: 'Title',
            content: static fn (Page $page): string => $page->getTitle(),
        ),
        new DataColumn(
            property: 'titlePath',
            header: 'Original Location',
            content: static fn (Page $page): string => $page->getParentLocation(),
        ),
        new DataColumn(
            property: 'deletedAt',
            header: 'Deleted At',
            content: static fn (Page $page): string => $page->getDeletedAt()->format('Y-m-d H:i:s'),
        ),
        new ActionColumn(
            buttons: [
                'restore' => new ActionButton(
                    Html::i('', ['class' => 'fa-solid fa-rotate-left']),
                    attributes: [
                        'title' => 'Restore',
                        'data-method' => 'POST',
                        'data-confirm' => 'Are you sure you want to restore this item?',
                    ],
                ),
            ],
            urlCreator: function ($action, $context) use ($urlGenerator) {
                return $urlGenerator->generate('atom.page.' . $action, ['uuid' => $context->data->getUuid()]);
            },
        ),
    )
?>

