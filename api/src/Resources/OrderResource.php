<?php

declare(strict_types=1);

namespace App\Resources;

use App\Entities\Order;
use App\Entities\OrderItem;
use App\Shared\MoneyConvertible;
use DateTimeInterface;
use JsonSerializable;

final class OrderResource implements JsonSerializable
{
    use MoneyConvertible;

    public function __construct(private readonly Order $order)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $items = $this->order->getItems()->toArray();
        $totalCents = array_sum(array_map(
            static fn (OrderItem $item) => $item->getPrice()->getValueCents() * $item->getQty(),
            $items,
        ));

        return [
            'id' => $this->order->getId()->toString(),
            'createdAt' => $this->order->getCreatedAt()->format(DateTimeInterface::ATOM),
            'items' => array_map(
                static fn (OrderItem $item) => new OrderItemResource($item),
                $items,
            ),
            'total' => $this->toMajorUnits((int) $totalCents),
        ];
    }
}
