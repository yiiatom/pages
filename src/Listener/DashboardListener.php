<?php

declare(strict_types=1);

namespace Atom\Pages\Listener;

use Atom\Dashboard\DashboardCard;
use Atom\Dashboard\DashboardCardItem;
use Atom\Dashboard\DashboardEvent;
use Atom\Pages\Repository\PageRepository;
use Yiisoft\Router\UrlGeneratorInterface;

final class DashboardListener
{
    public function __construct(
        private PageRepository $pageRepository,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function __invoke(DashboardEvent $event): void
    {
        $pageStats = $this->pageRepository->getSummaryStats();

        $card = new DashboardCard(
            title: 'Pages',
            icon: 'fa-solid fa-file-lines',
            items: [
                new DashboardCardItem('Total', (string) $pageStats['total']),
                new DashboardCardItem('Published', (string) $pageStats['published']),
                new DashboardCardItem('Drafts', (string) $pageStats['draft']),
                new DashboardCardItem(
                    label: 'In Trash',
                    value: (string) $pageStats['trash'],
                    status: $pageStats['trash'] ? 'warning' : 'default',
                ),
            ],
            order: 15,
            linkUrl: $this->urlGenerator->generate('atom.page.index'),
            linkText: 'Manage Pages',
        );

        $event->addCard($card);
    }
}
