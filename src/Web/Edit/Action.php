<?php

declare(strict_types=1);

namespace Atom\Pages\Web\Edit;

use Atom\Pages\Entity\PageStatus;
use Atom\Pages\Repository\PageRepository;
use Atom\Web\Shared\Breadcrumbs\BreadcrumbsProvider;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\FormModel\FormHydrator;
use Yiisoft\Http\Status;
use Yiisoft\Router\HydratorAttribute\RouteArgument;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\Flash\FlashInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final readonly class Action
{
    public function __construct(
        private BreadcrumbsProvider $breadcrumbsProvider,
        private FlashInterface $flash,
        private FormHydrator $formHydrator,
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

        if (!$page) {
            return $this->responseFactory
                ->createResponse(Status::NOT_FOUND);
        }

        $this->breadcrumbsProvider
            ->add('Pages', 'atom.page.index')
            ->add($page->getTitle());

        $parents = $this->pageRepository->getTreeAsDataReader()->read();

        $form = (new PageEditForm())
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
                $form->addError('Slug is already in use.', ['slug']);
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

            $this->flash->add('success', 'Page has been updated.');

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
                'form' => $form,
                'page' => $page,
            ]);
    }
}
