<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;

abstract class Controller
{
    protected function json(mixed $data, int $status = 200): Response
    {
        return new JsonResponse($data, $status);
    }
}
