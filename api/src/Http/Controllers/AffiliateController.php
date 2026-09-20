<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Entities\Affiliate;
use Psr\Http\Message\ResponseInterface as Response;

final class AffiliateController extends Controller
{
    public function __invoke(Affiliate $affiliate): Response
    {
        return $this->json(['data' => ['id' => $affiliate->getId()->toString(), 'name' => $affiliate->getName()]]);
    }
}
