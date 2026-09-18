<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cart;

use App\Exceptions\InvalidRequestException;
use App\Http\Controllers\Controller;
use App\Services\CartService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class CartItemDestroyController extends Controller
{
    use HasCartId;

    public function __construct(private readonly CartService $cartService)
    {
    }

    public function __invoke(Request $request): Response
    {
        $itemId = $request->getAttribute('itemId');
        if (!is_string($itemId)) {
            throw new InvalidRequestException('"itemId" is required.');
        }

        $data = $this->cartService->removeItem($this->affiliate($request), $this->cartId($request), $itemId);

        return $this->json(['data' => $data]);
    }
}
