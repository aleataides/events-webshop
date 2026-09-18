<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Entities\Affiliate;
use App\Exceptions\MissingAffiliateContextException;
use App\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

abstract class Controller
{
    protected function json(mixed $data, int $status = 200): Response
    {
        return new JsonResponse($data, $status);
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
