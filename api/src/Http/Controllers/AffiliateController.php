<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Entities\Affiliate;
use App\Resources\AffiliateResource;
use Psr\Http\Message\ResponseInterface as Response;

final class AffiliateController extends Controller
{
    public function __invoke(Affiliate $affiliate): Response
    {
        return $this->json(['data' => new AffiliateResource($affiliate)]);
    }
}
