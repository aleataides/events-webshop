<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cart;

use App\Entities\Affiliate;
use App\Entities\Cart;
use App\Http\Controllers\Controller;
use App\Resources\CartResource;
use App\Services\CartService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class CartController extends Controller
{
    use HasCartId;

    public function __construct(private readonly CartService $cartService)
    {
    }

    public function __invoke(Request $request, Affiliate $affiliate): Response
    {
        $cart = $this->cartService->getCart($affiliate, $this->cartId($request));

        return $this->json(['data' => $cart instanceof Cart ? new CartResource($cart) : null]);
    }
}
