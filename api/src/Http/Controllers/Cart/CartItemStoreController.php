<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cart;

use App\Entities\Affiliate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\CartItemStoreRequest;
use App\Resources\CartResource;
use App\Services\CartService;
use Psr\Http\Message\ResponseInterface as Response;

final class CartItemStoreController extends Controller
{
    use HasCartId;

    public function __construct(private readonly CartService $cartService)
    {
    }

    public function __invoke(CartItemStoreRequest $request, Affiliate $affiliate): Response
    {
        $body = $request->validated();

        $cart = $this->cartService->addItem(
            $affiliate,
            $this->cartId($request->request()),
            $body['priceId'],
            $body['qty'],
        );

        return $this->json(['data' => new CartResource($cart)], 201);
    }
}
