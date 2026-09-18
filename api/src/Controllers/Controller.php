<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Entities\Affiliate;
use App\Exceptions\MissingAffiliateContextException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response as Psr7Response;

abstract class Controller
{
    protected function json(mixed $data, int $status = 200): Response
    {
        $response = new Psr7Response($status);
        $response->getBody()->write(json_encode($data, JSON_THROW_ON_ERROR));

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * The "affiliate" request attribute set by AffiliateMiddleware.
     */
    protected function affiliate(Request $request): Affiliate
    {
        $affiliate = $request->getAttribute('affiliate');
        if (!$affiliate instanceof Affiliate) {
            throw new MissingAffiliateContextException();
        }

        return $affiliate;
    }
}
