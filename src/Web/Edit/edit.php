<?php

declare(strict_types=1);

use Atom\Pages\Entity\Page;
use Atom\Pages\Web\Shared\PagesAsset;
use Yiisoft\Html\Html;
use Yiisoft\FormModel\Field;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var PageCreateForm $form
 * @var TranslatorInterface $t
 * @var UrlGeneratorInterface $urlGenerator
 * @var Page $page
 */

$title = $t->translate('Edit Page');

$this->setTitle($title);

$assetManager->register(PagesAsset::class);

$htmlForm = Html::form()
    ->class('form-default form-page-create')
    ->post()
    ->attribute('data-translit-url', $urlGenerator->generate('atom.translit'))
    ->csrf($csrf);

?>
<h1><?= Html::encode($title) ?></h1>

<?= $htmlForm->open() ?>
    <?= Field::select($form, 'parentUuid')->optionsData($form->getParentUuidOptions()) ?>
    <?= Field::text($form, 'title')->autofocus() ?>
    <?= Field::text($form, 'slug') ?>
    <?= Field::select($form, 'status')->optionsData($form->getStatusOptions()) ?>
    <?= Field::textarea($form, 'content') ?>
    <div id="form-content-container" class="mb-2"></div>
    <?= Html::submitButton($t->translate('Save'))->class('btn btn-primary') ?>
    <?= Html::a($t->translate('Cancel'))
        ->url($urlGenerator->generate('atom.page.index'))
        ->class('btn btn-outline-secondary')
    ?>
<?= $htmlForm->close() ?>

<div class="mt-3">
    <?= Html::i($t->translate('Last Updated: {date}', [
        'date' => $page->getUpdatedAt()->format('Y-m-d H:i:s'),
    ])) ?>
</div>
