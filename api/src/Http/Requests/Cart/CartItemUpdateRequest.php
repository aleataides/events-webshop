<?php

declare(strict_types=1);

namespace App\Http\Requests\Cart;

use App\Http\Requests\FormRequest;

final class CartItemUpdateRequest extends FormRequest
{
    protected function rules(): array
    {
        return ['itemId' => 'string', 'qty' => 'int'];
    }

    /**
     * @return array<string, mixed>
     */
    protected function data(): array
    {
        return [...parent::data(), 'itemId' => $this->request()->getAttribute('itemId')];
    }
}
