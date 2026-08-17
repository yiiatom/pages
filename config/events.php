<?php

declare(strict_types=1);

use Atom\Entity\UserRole;
use Atom\Dashboard\DashboardEvent;
use Atom\Event\SidebarMenuEvent;
use Atom\Pages\Listener\DashboardListener;
use Atom\Web\Shared\Sidebar\SidebarMenuItem;

return [
    SidebarMenuEvent::class => [
        static function (SidebarMenuEvent $event) {
            $event->addItem(new SidebarMenuItem(
                label: 'Pages',
                routeName: 'atom.page.index',
                icon: 'fa-solid fa-file-lines',
                requiredRole: UserRole::ADMIN,
            ));
        }
    ],
    DashboardEvent::class => [
        [DashboardListener::class, '__invoke'],
    ],
];
