<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cart;

use Psr\Http\Message\ServerRequestInterface as Request;

trait HasCartId
{
    /**
     * The X-Cart-Id header, or null if absent — never a URL param, see
     * docs/shared/business-rules.md#cart-identity.
     */
    protected function cartId(Request $request): ?string
    {
        $cartId = $request->getHeaderLine('X-Cart-Id');

        return $cartId !== '' ? $cartId : null;
    }
}
