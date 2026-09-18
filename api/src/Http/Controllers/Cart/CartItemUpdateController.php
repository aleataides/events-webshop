<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cart;

use App\Exceptions\InvalidRequestException;
use App\Http\Controllers\Controller;
use App\Services\CartService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class CartItemUpdateController extends Controller
{
    use HasCartId;

    public function __construct(private readonly CartService $cartService)
    {
    }

    public function __invoke(Request $request): Response
    {
        $itemId = $request->getAttribute('itemId');
        $body = $request->getParsedBody();
        if (!is_string($itemId) || !is_array($body) || !is_int($body['qty'] ?? null)) {
            throw new InvalidRequestException('"qty" (int) is required.');
        }

        $data = $this->cartService->updateItemQty($this->affiliate($request), $this->cartId($request), $itemId, $body['qty']);

        return $this->json(['data' => $data]);
    }
}
