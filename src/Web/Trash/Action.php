<?php

declare(strict_types=1);

namespace Atom\Pages\Web\Trash;

use Atom\Breadcrumbs\Breadcrumb;
use Atom\Breadcrumbs\BreadcrumbsProvider;
use Atom\Pages\Repository\PageRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final class Action
{
    public function __construct(
        private BreadcrumbsProvider $breadcrumbsProvider,
        private PageRepository $pageRepository,
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function __invoke(
        ServerRequestInterface $request,
    ): ResponseInterface
    {
        $t = $this->translator->withDefaultCategory('atom-pages');

        $this->breadcrumbsProvider->add(
            new Breadcrumb(
                label: $t->translate('Pages'),
                url: $this->urlGenerator->generate('atom.page.index'),
            ),
            new Breadcrumb(
                label: $t->translate('Trash'),
            ),
        );

        $dataReader = $this->pageRepository->findAllDeletedAsDataReader();

        return $request
            ->getAttribute(WebViewRenderer::class)
            ->render(__DIR__ . '/trash', [
                't' => $t,
                'dataReader' => $dataReader,
            ]);
    }
}
