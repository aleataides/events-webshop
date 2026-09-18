<?php

declare(strict_types=1);

use App\Http\Controllers\Cart\CartController;
use App\Http\Controllers\Cart\CartItemDestroyController;
use App\Http\Controllers\Cart\CartItemStoreController;
use App\Http\Controllers\Cart\CartItemUpdateController;
use App\Http\Controllers\CategoryListController;
use App\Http\Controllers\EventDetailController;
use App\Http\Controllers\EventListController;
use App\Http\Middlewares\AffiliateMiddleware;
use Psr\Container\ContainerInterface;
use Slim\App;

/**
 * Registers the /api/{affiliateId}/... route group on the given Slim app.
 */
return static function (App $app, ContainerInterface $container): void {
    $app->group('/api/{affiliateId}', function ($group): void {
        $group->get('/events', EventListController::class);
        $group->get('/events/{eventId}', EventDetailController::class);
        $group->get('/categories', CategoryListController::class);

        $group->group('/cart', function ($cart): void {
            $cart->get('', CartController::class);
            $cart->post('/items', CartItemStoreController::class);
            $cart->patch('/items/{itemId}', CartItemUpdateController::class);
            $cart->delete('/items/{itemId}', CartItemDestroyController::class);
        });
    })->add($container->get(AffiliateMiddleware::class));
};
