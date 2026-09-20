<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class AffiliateController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $affiliate = $this->affiliate($request);

        return $this->json(['data' => ['id' => $affiliate->getId()->toString(), 'name' => $affiliate->getName()]]);
    }
}
