<?php

declare(strict_types=1);

namespace Atom\Pages\Web\Restore;

use Atom\Pages\Repository\PageRepository;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Http\Status;
use Yiisoft\Router\HydratorAttribute\RouteArgument;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\Flash\FlashInterface;

final readonly class Action
{
    public function __construct(
        private FlashInterface $flash,
        private PageRepository $pageRepository,
        private ResponseFactoryInterface $responseFactory,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function __invoke(
        #[RouteArgument('uuid')] string $uuid,
        ServerRequestInterface $request,
    ): ResponseInterface
    {
        $page = $this->pageRepository->findOneByUuid($uuid);

        if (!$page || !$page->isDeleted()) {
            return $this->responseFactory
                ->createResponse(Status::NOT_FOUND);
        }

        $items = $this->pageRepository->findAllParents($page);
        array_push($items, $page);

        foreach ($items as $item) {
            if ($item->isDeleted()) {
                $item->restore();
            }
        }

        $this->pageRepository->save($items);

        $this->flash->add('success', 'Page has been restored.');

        return $this->responseFactory
            ->createResponse(Status::SEE_OTHER)
            ->withHeader(
                'Location', 
                $this->urlGenerator->generate('atom.page.index'),
            );
    }
}
