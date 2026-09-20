<?php

declare(strict_types=1);

use App\Http\Middlewares\CorsMiddleware;
use App\Http\Middlewares\RateLimitMiddleware;
use App\Http\Middlewares\RequestIdMiddleware;
use App\Http\Routing\ControllerInvocationStrategy;
use App\Shared\ErrorHandler;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory;

/**
 * Builds the Slim app from the DI container: routes (routes/api.php),
 * middleware (outermost to innermost: RequestId, Cors, RateLimit), and the
 * custom error handler.
 */
return static function (ContainerInterface $container): App {
    AppFactory::setContainer($container);
    $app = AppFactory::create();
    $app->getRouteCollector()->setDefaultInvocationStrategy(new ControllerInvocationStrategy());

    (require __DIR__ . '/../routes/api.php')($app, $container);

    $app->addRoutingMiddleware();
    $app->addBodyParsingMiddleware();

    // Must sit inside RequestId/Cors/RateLimit, not just before them —
    // those decorate every response, but only see one if it isn't a thrown exception.
    $errorMiddleware = $app->addErrorMiddleware(false, false, false);
    $errorMiddleware->setDefaultErrorHandler($container->get(ErrorHandler::class));

    $app->add($container->get(RateLimitMiddleware::class));
    $app->add($container->get(CorsMiddleware::class));
    $app->add($container->get(RequestIdMiddleware::class));

    return $app;
};
