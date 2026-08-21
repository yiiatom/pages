<?php

declare(strict_types=1);

namespace Atom\Pages\Listener;

use Atom\Entity\UserRole;
use Atom\Sidebar\Event\SidebarMenuEvent;
use Atom\Sidebar\SidebarMenuItem;
use Yiisoft\Translator\TranslatorInterface;

final class SidebarMenuListener
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {}

    public function __invoke(SidebarMenuEvent $event): void
    {
        $t = $this->translator->withDefaultCategory('atom-pages');

        $event->addItem(new SidebarMenuItem(
            label: $t->translate('Pages'),
            routeName: 'atom.page.index',
            icon: 'fa-solid fa-file-lines',
            requiredRole: UserRole::ADMIN,
            priority: 10,
        ));
    }
}
