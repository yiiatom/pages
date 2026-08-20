<?php

declare(strict_types=1);

namespace Atom\Pages\Web\Delete;

use Atom\Pages\Repository\PageRepository;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Http\Status;
use Yiisoft\Router\HydratorAttribute\RouteArgument;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\Flash\FlashInterface;
use Yiisoft\Translator\TranslatorInterface;

final readonly class Action
{
    public function __construct(
        private FlashInterface $flash,
        private PageRepository $pageRepository,
        private ResponseFactoryInterface $responseFactory,
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function __invoke(
        #[RouteArgument('uuid')] string $uuid,
        ServerRequestInterface $request,
    ): ResponseInterface
    {
        $t = $this->translator->withDefaultCategory('atom-pages');

        $page = $this->pageRepository->findOneByUuid($uuid);

        if (!$page || $page->isDeleted()) {
            return $this->responseFactory
                ->createResponse(Status::NOT_FOUND);
        }

        $page->delete();

        $this->pageRepository->save($page);

        $this->flash->add('success', $t->translate('Page has been deleted.'));

        return $this->responseFactory
            ->createResponse(Status::SEE_OTHER)
            ->withHeader(
                'Location', 
                $this->urlGenerator->generate('atom.page.index'),
            );
    }
}
