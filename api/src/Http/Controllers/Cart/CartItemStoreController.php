<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cart;

use App\Exceptions\InvalidRequestException;
use App\Http\Controllers\Controller;
use App\Services\CartService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class CartItemStoreController extends Controller
{
    use HasCartId;

    public function __construct(private readonly CartService $cartService)
    {
    }

    public function __invoke(Request $request): Response
    {
        $body = $request->getParsedBody();
        if (!is_array($body) || !is_string($body['priceId'] ?? null) || !is_int($body['qty'] ?? null)) {
            throw new InvalidRequestException('"priceId" (string) and "qty" (int) are required.');
        }

        $data = $this->cartService->addItem($this->affiliate($request), $this->cartId($request), $body['priceId'], $body['qty']);

        return $this->json(['data' => $data], 201);
    }
}
