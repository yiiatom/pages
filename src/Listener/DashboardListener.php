<?php

declare(strict_types=1);

namespace Atom\Pages\Listener;

use Atom\Dashboard\DashboardCard;
use Atom\Dashboard\DashboardCardItem;
use Atom\Dashboard\Event\DashboardEvent;
use Atom\Pages\Repository\PageRepository;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

final class DashboardListener
{
    public function __construct(
        private PageRepository $pageRepository,
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function __invoke(DashboardEvent $event): void
    {
        $t = $this->translator->withDefaultCategory('atom-pages');

        $pageStats = $this->pageRepository->getSummaryStats();

        $card = new DashboardCard(
            title: $t->translate('Pages'),
            icon: 'fa-solid fa-file-lines',
            items: [
                new DashboardCardItem(
                    label: $t->translate('Total'),
                    value: (string) $pageStats['total'],
                ),
                new DashboardCardItem(
                    label: $t->translate('Published'),
                    value: (string) $pageStats['published'],
                ),
                new DashboardCardItem(
                    label: $t->translate('Drafts'),
                    value: (string) $pageStats['draft'],
                ),
                new DashboardCardItem(
                    label: $t->translate('In Trash'),
                    value: (string) $pageStats['trash'],
                    status: $pageStats['trash'] ? 'warning' : 'default',
                ),
            ],
            order: 15,
            linkUrl: $this->urlGenerator->generate('atom.page.index'),
            linkText: $t->translate('Manage Pages'),
        );

        $event->addCard($card);
    }
}
