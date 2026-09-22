<?php

declare(strict_types=1);

namespace App\Resources;

use App\Entities\OrderItem;
use App\Shared\MoneyConvertible;
use JsonSerializable;

final class OrderItemResource implements JsonSerializable
{
    use MoneyConvertible;

    public function __construct(private readonly OrderItem $item)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->item->getId()->toString(),
            'price' => new PriceResource($this->item->getPrice()),
            'qty' => $this->item->getQty(),
            'subtotal' => $this->toMajorUnits($this->item->getPrice()->getValueCents() * $this->item->getQty()),
        ];
    }
}
