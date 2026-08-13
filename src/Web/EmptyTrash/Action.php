<?php

declare(strict_types=1);

namespace Atom\Pages\Web\EmptyTrash;

use Atom\Pages\Repository\PageRepository;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Http\Status;
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
        ServerRequestInterface $request,
    ): ResponseInterface
    {
        $count = $this->pageRepository->purgeDeleted();

        if ($count === 0) {
            $this->flash->add('success', 'Trash is empty.');
        } else {
            $this->flash->add('success', 'Trash has been cleared.');
        }

        return $this->responseFactory
            ->createResponse(Status::SEE_OTHER)
            ->withHeader(
                'Location', 
                $this->urlGenerator->generate('atom.page.index'),
            );
    }
}
