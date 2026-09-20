<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cart;

use App\Entities\Affiliate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\CartItemUpdateRequest;
use App\Services\CartService;
use Psr\Http\Message\ResponseInterface as Response;

final class CartItemUpdateController extends Controller
{
    use HasCartId;

    public function __construct(private readonly CartService $cartService)
    {
    }

    public function __invoke(CartItemUpdateRequest $request, Affiliate $affiliate): Response
    {
        $body = $request->validated();

        $data = $this->cartService->updateItemQty(
            $affiliate,
            $this->cartId($request->request()),
            $body['itemId'],
            $body['qty'],
        );

        return $this->json(['data' => $data]);
    }
}
