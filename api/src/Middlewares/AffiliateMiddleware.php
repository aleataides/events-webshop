<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Entities\Affiliate;
use App\Exceptions\AffiliateNotFoundException;
use App\Shared\RequestContext;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Slim\Routing\RouteContext;

/**
 * Resolves {affiliateId} from the route and injects the Affiliate entity
 * into request context, so repositories/services stay affiliate-agnostic.
 */
final class AffiliateMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestContext $requestContext,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $affiliateId = (string) (RouteContext::fromRequest($request)->getRoute()?->getArgument('affiliateId'));

        try {
            $uuid = Uuid::fromString($affiliateId);
        } catch (InvalidUuidStringException) {
            throw new AffiliateNotFoundException($affiliateId);
        }

        $affiliate = $this->entityManager->find(Affiliate::class, $uuid);
        if (!$affiliate instanceof Affiliate) {
            throw new AffiliateNotFoundException($affiliateId);
        }

        $this->requestContext->setAffiliateId($affiliate->getId()->toString());

        return $handler->handle($request->withAttribute('affiliate', $affiliate));
    }
}
