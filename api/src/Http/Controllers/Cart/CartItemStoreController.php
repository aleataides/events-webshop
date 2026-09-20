<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cart;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\CartItemStoreRequest;
use App\Services\CartService;
use Psr\Http\Message\ResponseInterface as Response;

final class CartItemStoreController extends Controller
{
    use HasCartId;

    public function __construct(private readonly CartService $cartService)
    {
    }

    public function __invoke(CartItemStoreRequest $request): Response
    {
        $body = $request->validated();

        $data = $this->cartService->addItem(
            $this->affiliate($request->request()),
            $this->cartId($request->request()),
            $body['priceId'],
            $body['qty'],
        );

        return $this->json(['data' => $data], 201);
    }
}
