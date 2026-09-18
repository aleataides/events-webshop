<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\CartService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    public function __invoke(Request $request): Response
    {
        $affiliate = $this->affiliate($request);
        $cartId = $request->getHeaderLine('X-Cart-Id');

        $data = $this->cartService->getCart($affiliate, $cartId !== '' ? $cartId : null);

        return $this->json(['data' => $data]);
    }
}
