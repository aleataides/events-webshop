<?php

declare(strict_types=1);

namespace App\Resources;

use App\Entities\OrderItem;
use App\Shared\MoneyConvertible;

final class OrderItemResource
{
    use MoneyConvertible;

    public function __construct(private readonly OrderItem $item)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->item->getId()->toString(),
            'price' => new PriceResource($this->item->getPrice())->toArray(),
            'qty' => $this->item->getQty(),
            'subtotal' => $this->toMajorUnits($this->item->getPrice()->getValueCents() * $this->item->getQty()),
        ];
    }
}
