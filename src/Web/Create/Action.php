<?php

declare(strict_types=1);

namespace Atom\Pages\Web\Create;

use Atom\Pages\Entity\Page;
use Atom\Pages\Entity\PageStatus;
use Atom\Pages\Repository\PageRepository;
use Atom\Web\Shared\Breadcrumbs\BreadcrumbsProvider;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\FormModel\FormHydrator;
use Yiisoft\Http\Status;
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
        ServerRequestInterface $request,
    ): ResponseInterface
    {
        $this->breadcrumbsProvider
            ->add('Pages', 'atom.page.index')
            ->add('Create Page');

        $form = new PageCreateForm();
        $uuid = $request->getQueryParams()['uuid'] ?? null;

        if ($uuid) {
            $form->parentUuid = $uuid;
        }

        $parents = $this->pageRepository->getTreeAsDataReader()->read();
        $form = $form->withParents($parents);

        $this->formHydrator->populateFromPostAndValidate($form, $request);

        if ($form->isValid()) {
            if ($this->pageRepository->existsBySlug($form->slug, $form->parentUuid)) {
                $form->addError('Slug is already in use.', ['slug']);
            }
        }

        if ($form->isValid()) {
            $parent = $form->parentUuid ? $this->pageRepository->findOneByUuid($form->parentUuid) : null;

            $page = Page::create(
                title: $form->title,
                slug: $form->slug,
                position: $this->pageRepository->getNextPosition($form->parentUuid),
                content: $form->content,
                status: PageStatus::from($form->status),
                parent: $parent,
            );
            $this->pageRepository->save($page);

            $this->flash->add('success', 'Page has been created.');

            return $this->responseFactory
                ->createResponse(Status::SEE_OTHER)
                ->withHeader(
                    'Location', 
                    $this->urlGenerator->generate('atom.page.index'),
                );
        }

        return $request
            ->getAttribute(WebViewRenderer::class)
            ->render(__DIR__ . '/create', [
                'form' => $form,
            ]);
    }
}
