<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cart;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class CartBuyController extends Controller
{
    use HasCartId;

    public function __construct(private readonly CartService $cartService)
    {
    }

    public function __invoke(Request $request): Response
    {
        $data = $this->cartService->buy($this->affiliate($request), $this->cartId($request));

        return $this->json(['data' => $data], 201);
    }
}
