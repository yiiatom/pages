<?php

declare(strict_types=1);

namespace Atom\Pages\Listener;

use Atom\Entity\UserRole;
use Atom\Sidebar\Event\SidebarMenuEvent;
use Atom\Sidebar\SidebarMenuItem;

final class SidebarMenuListener
{
    public function __construct(
    ) {}

    public function __invoke(SidebarMenuEvent $event): void
    {
        $event->addItem(new SidebarMenuItem(
            label: 'Pages',
            routeName: 'atom.page.index',
            icon: 'fa-solid fa-file-lines',
            requiredRole: UserRole::ADMIN,
        ));
    }
}
