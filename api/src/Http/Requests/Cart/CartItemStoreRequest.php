<?php

declare(strict_types=1);

namespace App\Http\Requests\Cart;

use App\Http\Requests\FormRequest;

final class CartItemStoreRequest extends FormRequest
{
    protected function rules(): array
    {
        return ['priceId' => 'string', 'qty' => 'int'];
    }
}
