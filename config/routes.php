<?php

declare(strict_types=1);

use Atom\Middleware\AccessControl;
use Atom\Middleware\Authentication;
use Atom\Middleware\MainTheme;
use Yiisoft\Http\Method;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;
use Yiisoft\User\Login\Cookie\CookieLoginMiddleware;

return [
    Group::create('/cms')
        ->middleware(CookieLoginMiddleware::class)
        ->middleware(MainTheme::class)
        ->middleware(Authentication::class)
        ->middleware(AccessControl::class)
        ->routes(
            Route::get('/pages')
                ->action(Atom\Pages\Web\Index\Action::class)
                ->name('atom.page.index'),

            Route::methods([Method::GET, Method::POST], '/pages/create')
                ->action(Atom\Pages\Web\Create\Action::class)
                ->name('atom.page.create'),

            Route::methods([Method::GET, Method::POST], '/pages/{uuid}/edit')
                ->action(Atom\Pages\Web\Edit\Action::class)
                ->name('atom.page.edit'),

            Route::post('/pages/{uuid}/delete')
                ->action(Atom\Pages\Web\Delete\Action::class)
                ->name('atom.page.delete'),

            Route::post('/pages/sort')
                ->action(Atom\Pages\Web\Sort\Action::class)
                ->name('atom.page.sort'),

            Route::get('/pages/trash')
                ->action(Atom\Pages\Web\Trash\Action::class)
                ->name('atom.page.trash'),

            Route::post('/pages/{uuid}/restore')
                ->action(Atom\Pages\Web\Restore\Action::class)
                ->name('atom.page.restore'),
        ),
];
