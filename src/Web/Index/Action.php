<?php

declare(strict_types=1);

namespace Atom\Pages\Web\Index;

use Atom\Breadcrumbs\Breadcrumb;
use Atom\Breadcrumbs\BreadcrumbsProvider;
use Atom\Pages\Repository\PageRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final class Action
{
    public function __construct(
        private BreadcrumbsProvider $breadcrumbsProvider,
        private PageRepository $pageRepository,
        private TranslatorInterface $translator,
    ) {}

    public function __invoke(
        ServerRequestInterface $request,
    ): ResponseInterface
    {
        $t = $this->translator->withDefaultCategory('atom-pages');

        $this->breadcrumbsProvider->add(
            new Breadcrumb(
                label: $t->translate('Pages'),
            ),
        );

        $dataReader = $this->pageRepository->findTreeAsDataReader();
        $deletedCount = $this->pageRepository->getDeletedCount();

        return $request
            ->getAttribute(WebViewRenderer::class)
            ->render(__DIR__ . '/index', [
                't' => $t,
                'dataReader' => $dataReader,
                'deletedCount' => $deletedCount,
            ]);
    }
}
