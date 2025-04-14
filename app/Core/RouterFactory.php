<?php

declare(strict_types=1);

namespace App\Core;

use Nette\Application\Routers\RouteList;

final class RouterFactory
{
    public static function createRouter(): RouteList
    {
        $router = new RouteList;

        // Admin modul
        $adminRouter = new RouteList('Admin');
        $adminRouter->addRoute('admin/<presenter>/<action>[/<id>]', 'Dashboard:default');
        $router->add($adminRouter);


        // Front modul
        $frontRouter = new RouteList('Front');
        $frontRouter->addRoute('<presenter>/<action>[/<id>]', 'Home:default');
        $router->add($frontRouter);

        return $router;
    }
}
