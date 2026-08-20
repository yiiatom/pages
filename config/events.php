<?php

declare(strict_types=1);

use Atom\Entity\UserRole;
use Atom\Dashboard\Event\DashboardEvent;
use Atom\Pages\Listener\DashboardListener;
use Atom\Pages\Listener\SidebarMenuListener;
use Atom\Sidebar\Event\SidebarMenuEvent;
use Atom\Sidebar\SidebarMenuItem;

return [
    SidebarMenuEvent::class => [
        [SidebarMenuListener::class, '__invoke'],
    ],
    DashboardEvent::class => [
        [DashboardListener::class, '__invoke'],
    ],
];
