<?php

declare(strict_types=1);

use Atom\Pages\Entity\Page;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\DataView\GridView\Column\ActionButton;
use Yiisoft\Yii\DataView\GridView\Column\ActionColumn;
use Yiisoft\Yii\DataView\GridView\Column\DataColumn;
use Yiisoft\Yii\DataView\GridView\GridView;

/**
 * @var DataReaderInterface $dataReader
 * @var TranslatorInterface $t
 * @var UrlGeneratorInterface $urlGenerator
 */

$title = $t->translate('Trash');

$this->setTitle($title);

?>
<h1><?= Html::encode($title) ?></h1>

<?php if ($dataReader->count() > 0): ?>
    <div class="mb-2">
        <?= Html::a(Html::i()->class('fa-solid fa-trash-can me-2')->render() . Html::encode($t->translate('Empty Trash')))
            ->url($urlGenerator->generate('atom.page.empty-trash'))
            ->class('btn btn-danger')
            ->addAttributes([
                'data-method' => 'POST',
                'data-confirm' => $t->translate('Are you sure you want to permanently delete all items in the trash? This action cannot be undone.'),
            ])
            ->encode(false)
        ?>
    </div>
<?php endif; ?>

<?= GridView::widget()
    ->dataReader($dataReader)
    ->columns(
        new DataColumn(
            property: 'title',
            header: $t->translate('Title'),
            content: static fn (Page $page): string => $page->getTitle(),
        ),
        new DataColumn(
            property: 'titlePath',
            header: $t->translate('Original Location'),
            content: static fn (Page $page): string => $page->getParentLocation(),
        ),
        new DataColumn(
            property: 'deletedAt',
            header: $t->translate('Deleted At'),
            content: static fn (Page $page): string => $page->getDeletedAt()->format('Y-m-d H:i:s'),
        ),
        new ActionColumn(
            buttons: [
                'restore' => new ActionButton(
                    Html::i('', ['class' => 'fa-solid fa-rotate-left']),
                    attributes: [
                        'title' => $t->translate('Restore'),
                        'data-method' => 'POST',
                        'data-confirm' => $t->translate('Are you sure you want to restore this page?'),
                    ],
                ),
            ],
            urlCreator: function ($action, $context) use ($urlGenerator) {
                return $urlGenerator->generate('atom.page.' . $action, ['uuid' => $context->data->getUuid()]);
            },
        ),
    )
?>

