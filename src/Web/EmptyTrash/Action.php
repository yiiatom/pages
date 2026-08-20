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
        ServerRequestInterface $request,
    ): ResponseInterface
    {
        $t = $this->translator->withDefaultCategory('atom-pages');

        $count = $this->pageRepository->purgeDeleted();

        if ($count === 0) {
            $this->flash->add('success', $t->translate('Trash is empty.'));
        } else {
            $this->flash->add('success', $t->translate('Trash has been cleared.'));
        }

        return $this->responseFactory
            ->createResponse(Status::SEE_OTHER)
            ->withHeader(
                'Location', 
                $this->urlGenerator->generate('atom.page.index'),
            );
    }
}
