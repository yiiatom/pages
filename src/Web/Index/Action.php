<?php

declare(strict_types=1);

namespace Atom\Pages\Web\Index;

use Atom\Pages\Repository\PageRepository;
use Atom\Web\Shared\Breadcrumbs\BreadcrumbsProvider;
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
        $this->breadcrumbsProvider->add('Pages');

        $dataReader = $this->pageRepository->findTreeAsDataReader();

        return $request
            ->getAttribute(WebViewRenderer::class)
            ->render(__DIR__ . '/index', [
                'dataReader' => $dataReader,
            ]);
    }
}