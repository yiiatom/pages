<?php

declare(strict_types=1);

namespace Atom\Pages\Web\Index;

use Atom\Breadcrumbs\Breadcrumb;
use Atom\Breadcrumbs\BreadcrumbsProvider;
use Atom\Pages\Repository\PageRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final class Action
{
    public function __construct(
        private BreadcrumbsProvider $breadcrumbsProvider,
        private PageRepository $pageRepository
    ) {}

    public function __invoke(
        ServerRequestInterface $request,
    ): ResponseInterface
    {
        $this->breadcrumbsProvider->add(
            new Breadcrumb(
                label: 'Pages',
            ),
        );

        $dataReader = $this->pageRepository->findTreeAsDataReader();
        $deletedCount = $this->pageRepository->getDeletedCount();

        return $request
            ->getAttribute(WebViewRenderer::class)
            ->render(__DIR__ . '/index', [
                'dataReader' => $dataReader,
                'deletedCount' => $deletedCount,
            ]);
    }
}
