<?php

declare(strict_types=1);

use Atom\Pages\Entity\Page;
use Atom\Pages\Web\Shared\PagesAsset;
use Yiisoft\Html\Html;
use Yiisoft\Yii\DataView\GridView\Column\ActionButton;
use Yiisoft\Yii\DataView\GridView\Column\ActionColumn;
use Yiisoft\Yii\DataView\GridView\Column\DataColumn;
use Yiisoft\Yii\DataView\GridView\GridView;

$title = 'Pages';

$this->setTitle($title);

$assetManager->register(PagesAsset::class);

?>
<h1><?= Html::encode($title) ?></h1>

<div class="mb-2 d-flex justify-content-between align-items-center">
    <?= Html::a('Create Page')
        ->url($urlGenerator->generate('atom.page.create'))
        ->class('btn btn-primary me-2')
    ?>
    <?= Html::a(Html::i()->class('fa-solid fa-trash-can me-2')->render() . 'Trash')
        ->url($urlGenerator->generate('atom.page.trash'))
        ->class('btn btn-outline-secondary')
        ->encode(false)
    ?>
</div>

<?= GridView::widget()
    ->dataReader($dataReader)
    ->tbodyAttributes(['data-sort-url' => $urlGenerator->generate('atom.page.sort')])
    ->addTbodyClass('sortable-tree')
    ->bodyRowAttributes(static function (Page $page): array {
        return [
            'data-uuid' => $page->getUuid(),
            'data-depth' => $page->getDepth(),
        ];
    })
    ->columns(
        new DataColumn(
            bodyAttributes: ['style' => 'width: 40px'],
            content: static function (Page $page): string {
                return Html::i()
                    ->class('fa-solid fa-grip-vertical opacity-50 sort-handle')
                    ->render();
            },
            encodeContent: false,
        ),
        new DataColumn(
            property: 'title',
            header: 'Title',
            content: static function (Page $page): string {
                $depth = $page->getDepth();

                $padding = $depth * 24;

                if ($depth > 0) {
                    $icon = '<i class="fa-solid fa-turn-up fa-rotate-90 text-muted opacity-50 small me-2" style="font-size: 0.8rem;"></i>';
                } else {
                    $icon = '<i class="fa-solid fa-folder text-primary opacity-75 me-2"></i>';
                }

                return Html::div($icon . Html::encode($page->getTitle()))
                    ->encode(false)
                    ->addStyle('padding-left: ' . $padding . 'px')
                    ->render();
            },
            encodeContent: false,
        ),
        new DataColumn(
            property: 'status',
            header: 'Status',
            content: static function (Page $page): string {
                $status = $page->getStatus();

                $options = ['class' => 'badge'];
                Html::addCssClass($options, $status->getCssClass());

                return Html::span(
                    Html::encode($status->getLabel()),
                    $options,
                )->render();
            },
            encodeContent: false,
        ),
        new ActionColumn(
            buttons: [
                'create' => new ActionButton(
                    Html::i('', ['class' => 'fa-solid fa-folder-plus']),
                    attributes: ['title' => 'Add Page Here'],
                ),
                'edit' => new ActionButton(
                    Html::i('', ['class' => 'fa-solid fa-pencil']),
                    attributes: ['title' => 'Edit'],
                ),
                'delete' => new ActionButton(
                    Html::i('', ['class' => 'fa-solid fa-trash']),
                    attributes: [
                        'title' => 'Delete',
                        'data-method' => 'POST',
                        'data-confirm' => 'Are you sure you want to delete this item?',
                    ],
                ),
            ],
            urlCreator: function ($action, $context) use ($urlGenerator) {
                return $urlGenerator->generate('atom.page.' . $action, ['uuid' => $context->data->getUuid()]);
            },
        ),
    )
?>
