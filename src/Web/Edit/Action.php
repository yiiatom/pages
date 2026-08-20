<?php

declare(strict_types=1);

namespace Atom\Pages\Web\Edit;

use Atom\Breadcrumbs\Breadcrumb;
use Atom\Breadcrumbs\BreadcrumbsProvider;
use Atom\Pages\Entity\PageStatus;
use Atom\Pages\Repository\PageRepository;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\FormModel\FormHydrator;
use Yiisoft\Http\Status;
use Yiisoft\Router\HydratorAttribute\RouteArgument;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\Flash\FlashInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final readonly class Action
{
    public function __construct(
        private BreadcrumbsProvider $breadcrumbsProvider,
        private FlashInterface $flash,
        private FormHydrator $formHydrator,
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

        if (!$page) {
            return $this->responseFactory
                ->createResponse(Status::NOT_FOUND);
        }

        $this->breadcrumbsProvider->add(
            new Breadcrumb(
                label: $t->translate('Pages'),
                url: $this->urlGenerator->generate('atom.page.index'),
            ),
            new Breadcrumb(
                label: $page->getTitle(),
            ),
        );

        $parents = $this->pageRepository->findTreeAsDataReader()->read();

        $form = (new PageEditForm())
            ->withTranslator($t)
            ->withPath($page->getPath())
            ->withParents($parents);

        $form->parentUuid = $page->getParentUuid();
        $form->title = $page->getTitle();
        $form->slug = $page->getSlug();
        $form->content = $page->getContent();
        $form->status = $page->getStatus()->value;

        $this->formHydrator->populateFromPostAndValidate($form, $request);

        if ($form->isValid()) {
            if ($this->pageRepository->existsBySlug($form->slug, $form->parentUuid, $page->getUuid())) {
                $form->addError($t->translate('Slug is already in use.'), ['slug']);
            }
        }

        if ($form->isValid()) {
            $parent = $form->parentUuid ? $this->pageRepository->findOneByUuid($form->parentUuid) : null;

            $page->update(
                title: $form->title,
                slug: $form->slug,
                content: $form->content,
                status: PageStatus::from($form->status),
                parent: $parent,
            );
            $this->pageRepository->save($page);

            $this->flash->add('success', $t->translate('Page has been updated.'));

            return $this->responseFactory
                ->createResponse(Status::SEE_OTHER)
                ->withHeader(
                    'Location', 
                    $this->urlGenerator->generate('atom.page.index'),
                );
        }

        return $request
            ->getAttribute(WebViewRenderer::class)
            ->render(__DIR__ . '/edit', [
                't' => $t,
                'form' => $form,
                'page' => $page,
            ]);
    }
}
